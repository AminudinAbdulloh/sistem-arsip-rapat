<?php

use App\Models\NotulensiRapatModel;
use App\Models\UndanganRapatModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Pengujian White-Box (unit) untuk App\Models\NotulensiRapatModel.
 * Menguji hasil join findAllWithRelations()/findByIdWithRelations()
 * serta jalur "data tidak ditemukan".
 *
 * @internal
 */
final class NotulensiRapatModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    private int $userId;
    private int $undanganId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('users')->insert([
            'nip'        => '198001012005011001',
            'nama'       => 'Administrator ITD',
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
            'jabatan'    => 'Kepala Program Studi',
        ]);
        $this->userId = (int) $this->db->insertID();

        $undanganModel = new UndanganRapatModel();
        $this->undanganId = (int) $undanganModel->insert([
            'hari'       => 'Senin',
            'waktu'      => '2026-10-05 09:00:00',
            'tempat'     => 'Ruang Rapat Prodi',
            'acara'      => 'Rapat Koordinasi Kurikulum',
            'created_by' => $this->userId,
        ]);
    }

    public function testFindAllWithRelationsMengembalikanDataGabunganYangBenar(): void
    {
        $model = new NotulensiRapatModel();
        $model->insert([
            'undangan_id'     => $this->undanganId,
            'deskripsi_rapat' => 'Pembahasan kurikulum semester ganjil',
            'catatan'         => 'Tidak ada catatan tambahan',
            'created_by'      => $this->userId,
        ]);

        $hasil = $model->findAllWithRelations();

        $this->assertCount(1, $hasil);
        $this->assertSame('Rapat Koordinasi Kurikulum', $hasil[0]['nama_undangan']);
        $this->assertSame('Administrator ITD', $hasil[0]['created_by_nama']);
    }

    public function testFindByIdWithRelationsMengembalikanNullSaatTidakDitemukan(): void
    {
        $model = new NotulensiRapatModel();

        $this->assertNull($model->findByIdWithRelations(9999));
    }

    public function testFindByIdWithRelationsMengembalikanDetailYangBenar(): void
    {
        $model = new NotulensiRapatModel();
        $id    = (int) $model->insert([
            'undangan_id'     => $this->undanganId,
            'deskripsi_rapat' => 'Pembahasan kurikulum semester ganjil',
            'dokumentasi'     => json_encode(['dok_1.jpg', 'dok_2.jpg']),
            'created_by'      => $this->userId,
        ]);

        $detail = $model->findByIdWithRelations($id);

        $this->assertNotNull($detail);
        $this->assertSame('Ruang Rapat Prodi', $detail['tempat']);
        $this->assertSame(json_encode(['dok_1.jpg', 'dok_2.jpg']), $detail['dokumentasi']);
    }

    /**
     * Form notulensi tidak lagi mengisi tgl_rapat (NULL); tanggal rapat = waktu undangan.
     * Notulensi dibuat lewat model persis seperti NotulensiController::store().
     */
    private function buatNotulensi(string $waktuUndangan, string $acara): int
    {
        $undanganId = (int) (new UndanganRapatModel())->insert([
            'hari' => 'Senin', 'waktu' => $waktuUndangan, 'tempat' => 'Ruang X',
            'acara' => $acara, 'created_by' => $this->userId,
        ]);

        return (int) (new NotulensiRapatModel())->insert([
            'undangan_id' => $undanganId, 'deskripsi_rapat' => 'Isi ' . $acara, 'created_by' => $this->userId,
        ]);
    }

    public function testHitunganMemakaiTanggalUndanganMeskiTglRapatKosong(): void
    {
        $this->buatNotulensi('2026-10-05 09:00:00', 'Rapat Oktober');
        $this->buatNotulensi('2026-11-02 09:00:00', 'Rapat November');
        $this->buatNotulensi('2025-10-06 09:00:00', 'Rapat Oktober Tahun Lalu');
        $model = new NotulensiRapatModel();

        $this->assertSame(1, $model->countByMonth(10, 2026));
        $this->assertSame(1, $model->countByMonth(11, 2026));
        $this->assertSame(0, $model->countByMonth(12, 2026));
        $this->assertSame(2, $model->countByYear(2026));
        $this->assertSame(1, $model->countByYear(2025));
        $this->assertSame(0, $model->countByYear(2024));
    }

    public function testTglRapatLamaTidakMemengaruhiHitungan(): void
    {
        $id = $this->buatNotulensi('2026-10-05 09:00:00', 'Rapat Oktober');
        // data lama yang masih menyimpan tgl_rapat berbeda dengan tanggal undangan
        $this->db->table('notulensi_rapat')->where('id', $id)->update(['tgl_rapat' => '2020-01-15']);
        $model = new NotulensiRapatModel();

        $this->assertSame(1, $model->countByMonth(10, 2026));
        $this->assertSame(0, $model->countByMonth(1, 2020));
        $this->assertSame(0, $model->countByYear(2020));
    }

    public function testFindByMonthMengembalikanNotulensiBulanUndanganBerurutNaik(): void
    {
        $this->buatNotulensi('2026-10-20 09:00:00', 'Rapat Akhir Oktober');
        $this->buatNotulensi('2026-10-05 09:00:00', 'Rapat Awal Oktober');
        $this->buatNotulensi('2026-11-02 09:00:00', 'Rapat November');

        $hasil = (new NotulensiRapatModel())->findByMonth(10, 2026);

        $this->assertSame(['Rapat Awal Oktober', 'Rapat Akhir Oktober'], array_column($hasil, 'nama_undangan'));
        $this->assertSame('2026-10-05 09:00:00', $hasil[0]['waktu_undangan']);
        $this->assertSame('Ruang X', $hasil[0]['tempat']);
        $this->assertSame('Administrator ITD', $hasil[0]['created_by_nama']);
    }

    public function testFindByYearMengembalikanNotulensiTahunUndanganBerurutNaik(): void
    {
        $this->buatNotulensi('2026-11-02 09:00:00', 'Rapat November');
        $this->buatNotulensi('2026-03-02 09:00:00', 'Rapat Maret');
        $this->buatNotulensi('2025-12-30 09:00:00', 'Rapat Tahun Lalu');

        $hasil = (new NotulensiRapatModel())->findByYear(2026);

        $this->assertSame(['Rapat Maret', 'Rapat November'], array_column($hasil, 'nama_undangan'));
    }
}
