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
}
