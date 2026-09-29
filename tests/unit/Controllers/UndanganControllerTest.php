<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pengujian White-Box terhadap UndanganController::store() dan delete().
 *
 * store() memiliki dua jalur: validasi field kosong (gagal) dan penyimpanan
 * data lengkap (berhasil). delete() memiliki dua jalur: ditolak karena
 * undangan sudah memiliki notulensi (referential integrity), dan berhasil
 * dihapus karena belum memiliki notulensi.
 *
 * @internal
 */
final class UndanganControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private int $userId;

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
            ],
        ];
    }

    public function testJalurStoreFieldKosongDitolakDanTidakMenyimpanData(): void
    {
        $result = $this->withSession($this->sessionLogin())->post('/undangan/store', [
            'hari'   => '',
            'waktu'  => '',
            'tempat' => '',
            'acara'  => '',
        ]);

        $result->assertRedirectTo('/undangan/create');
        $result->assertSessionHas('error', 'Semua field wajib diisi.');
        $this->assertSame(0, $this->db->table('undangan_rapat')->countAllResults());
    }

    public function testJalurStoreDataLengkapBerhasilDisimpan(): void
    {
        $result = $this->withSession($this->sessionLogin())->post('/undangan/store', [
            'hari'   => 'Senin',
            'waktu'  => '2026-10-05T09:00',
            'tempat' => 'Ruang Rapat Prodi Informatika',
            'acara'  => 'Rapat Koordinasi Kurikulum',
        ]);

        $result->assertRedirectTo('/undangan');
        $result->assertSessionHas('success', 'Undangan rapat berhasil ditambahkan.');
        $this->seeInDatabase('undangan_rapat', ['tempat' => 'Ruang Rapat Prodi Informatika']);
    }

    public function testJalurDeleteDitolakKarenaUndanganSudahMemilikiNotulensi(): void
    {
        $this->db->table('undangan_rapat')->insert([
            'hari'       => 'Senin',
            'waktu'      => '2026-10-05 09:00:00',
            'tempat'     => 'Ruang Rapat Prodi',
            'acara'      => 'Rapat Uji',
            'created_by' => $this->userId,
        ]);
        $undanganId = (int) $this->db->insertID();

        $this->db->table('notulensi_rapat')->insert([
            'undangan_id'     => $undanganId,
            'deskripsi_rapat' => 'Hasil rapat uji',
            'created_by'      => $this->userId,
        ]);

        $result = $this->withSession($this->sessionLogin())->post("/undangan/{$undanganId}/delete");

        $result->assertRedirectTo('/undangan');
        $result->assertSessionHas('error', 'Undangan tidak dapat dihapus karena sudah memiliki notulensi.');
        $this->seeInDatabase('undangan_rapat', ['id' => $undanganId]);
    }

    public function testJalurDeleteBerhasilKarenaUndanganBelumMemilikiNotulensi(): void
    {
        $this->db->table('undangan_rapat')->insert([
            'hari'       => 'Senin',
            'waktu'      => '2026-10-05 09:00:00',
            'tempat'     => 'Ruang Rapat Prodi',
            'acara'      => 'Rapat Uji Tanpa Notulensi',
            'created_by' => $this->userId,
        ]);
        $undanganId = (int) $this->db->insertID();

        $result = $this->withSession($this->sessionLogin())->post("/undangan/{$undanganId}/delete");

        $result->assertRedirectTo('/undangan');
        $result->assertSessionHas('success', 'Undangan rapat berhasil dihapus.');
        $this->dontSeeInDatabase('undangan_rapat', ['id' => $undanganId]);
    }
}
