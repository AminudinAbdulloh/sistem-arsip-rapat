<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pengujian White-Box terhadap NotulensiController::store() untuk jalur-jalur
 * yang tidak melibatkan unggah berkas (validasi field, undangan tidak ditemukan,
 * dan aturan satu undangan hanya boleh memiliki satu notulensi). Jalur validasi
 * tipe berkas (uploadFoto) diuji secara terpisah sebagai unit test murni pada
 * NotulensiUploadFotoTest karena memerlukan objek UploadedFile pengganti (stub).
 *
 * @internal
 */
final class NotulensiControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

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

        $this->db->table('undangan_rapat')->insert([
            'hari'       => 'Senin',
            'waktu'      => '2026-10-05 09:00:00',
            'tempat'     => 'Ruang Rapat Prodi',
            'acara'      => 'Rapat Koordinasi Kurikulum',
            'created_by' => $this->userId,
        ]);
        $this->undanganId = (int) $this->db->insertID();
    }

    /** @return array<string, mixed> */
    private function sessionLogin(): array
    {
        return [
            'user' => [
                'id'      => $this->userId,
                'nip'     => '198001012005011001',
                'nama'    => 'Administrator ITD',
                'jabatan' => 'Kepala Program Studi',
                'role'    => 'admin',
            ],
        ];
    }

    public function testJalurStoreFieldWajibKosongDitolak(): void
    {
        $result = $this->withSession($this->sessionLogin())->post('/notulensi/store', [
            'undangan_id'     => 0,
            'deskripsi_rapat' => '',
        ]);

        $result->assertRedirectTo('/notulensi/create');
        $result->assertSessionHas('error', 'Field yang wajib diisi belum lengkap.');
        $this->assertSame(0, $this->db->table('notulensi_rapat')->countAllResults());
    }

    public function testJalurStoreUndanganTidakDitemukanDitolak(): void
    {
        $result = $this->withSession($this->sessionLogin())->post('/notulensi/store', [
            'undangan_id'     => 999999,
            'deskripsi_rapat' => 'Deskripsi rapat uji',
        ]);

        $result->assertRedirectTo('/notulensi/create');
        $result->assertSessionHas('error', 'Undangan tidak ditemukan.');
    }

    public function testJalurStoreUndanganSudahMemilikiNotulensiDitolak(): void
    {
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id'     => $this->undanganId,
            'deskripsi_rapat' => 'Notulensi pertama',
            'created_by'      => $this->userId,
        ]);

        $result = $this->withSession($this->sessionLogin())->post('/notulensi/store', [
            'undangan_id'     => $this->undanganId,
            'deskripsi_rapat' => 'Notulensi kedua untuk undangan yang sama',
        ]);

        $result->assertRedirectTo('/notulensi/create');
        $result->assertSessionHas('error', 'Undangan rapat ini sudah memiliki notulensi. Silakan pilih undangan lain.');
        $this->assertSame(1, $this->db->table('notulensi_rapat')->countAllResults());
    }

    public function testJalurStoreBerhasilTanpaDokumentasiSaatTidakAdaBerkasDiunggah(): void
    {
        $result = $this->withSession($this->sessionLogin())->post('/notulensi/store', [
            'undangan_id'     => $this->undanganId,
            'deskripsi_rapat' => 'Deskripsi rapat uji tanpa dokumentasi',
        ]);

        $result->assertRedirectTo('/notulensi');
        $result->assertSessionHas('success', 'Notulensi rapat berhasil ditambahkan.');
        $this->seeInDatabase('notulensi_rapat', [
            'undangan_id' => $this->undanganId,
            'dokumentasi' => null,
        ]);
    }
}
