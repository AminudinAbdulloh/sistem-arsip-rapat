<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Arsip rapat: hanya notulensi terverifikasi yang dapat dilihat dan dicari.
 *
 * @internal
 */
final class ArsipControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private int $userId;
    private int $verifiedId;
    private int $pendingId;
    private int $rejectedId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('users')->insert([
            'nip' => '10', 'nama' => 'Sekretaris Uji', 'jabatan' => 'X', 'role' => 'sekretaris',
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
        ]);
        $this->userId = (int) $this->db->insertID();

        $this->verifiedId = $this->createNotulensi('Rapat Kurikulum', 'Gedung A', '2026-03-10 09:00:00', 'Pembahasan mata kuliah baru', 'terverifikasi');
        $this->createNotulensi('Rapat Akreditasi', 'Gedung B', '2026-08-20 13:00:00', 'Persiapan borang akreditasi', 'terverifikasi');
        $this->pendingId  = $this->createNotulensi('Rapat Rahasia Menunggu', 'Gedung C', '2026-05-01 09:00:00', 'Belum diverifikasi', 'menunggu');
        $this->rejectedId = $this->createNotulensi('Rapat Ditolak', 'Gedung D', '2026-06-01 09:00:00', 'Ditolak kaprodi', 'ditolak');
    }

    private function createNotulensi(string $acara, string $tempat, string $waktu, string $deskripsi, string $status): int
    {
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => $waktu, 'tempat' => $tempat, 'acara' => $acara, 'created_by' => $this->userId,
        ]);
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => (int) $this->db->insertID(), 'deskripsi_rapat' => $deskripsi,
            'created_by' => $this->userId, 'status_verifikasi' => $status,
        ]);

        return (int) $this->db->insertID();
    }

    private function asDosen()
    {
        return $this->withSession(['user' => [
            'id' => 50, 'nip' => 'd', 'nama' => 'Dosen Uji', 'jabatan' => 'Dosen', 'foto_profil' => null, 'role' => 'dosen',
        ]]);
    }

    public function testBelumLoginDiarahkanKeLogin(): void
    {
        $this->get('/arsip')->assertRedirectTo('/login');
    }

    public function testDaftarHanyaMemuatArsipTerverifikasi(): void
    {
        $body = $this->asDosen()->get('/arsip')->getBody();

        $this->assertStringContainsString('Rapat Kurikulum', $body);
        $this->assertStringContainsString('Rapat Akreditasi', $body);
        $this->assertStringNotContainsString('Rapat Rahasia Menunggu', $body);
        $this->assertStringNotContainsString('Rapat Ditolak', $body);
    }

    public function testPencarianKataKunciPadaAcaraTempatDanDeskripsi(): void
    {
        $byAcara = $this->asDosen()->get('/arsip?q=Akreditasi')->getBody();
        $this->assertStringContainsString('Rapat Akreditasi', $byAcara);
        $this->assertStringNotContainsString('Rapat Kurikulum', $byAcara);

        $byTempat = $this->asDosen()->get('/arsip?q=Gedung+A')->getBody();
        $this->assertStringContainsString('Rapat Kurikulum', $byTempat);
        $this->assertStringNotContainsString('Rapat Akreditasi', $byTempat);

        $byDeskripsi = $this->asDosen()->get('/arsip?q=borang')->getBody();
        $this->assertStringContainsString('Rapat Akreditasi', $byDeskripsi);
    }

    public function testPencarianTidakMenemukanArsipYangBelumTerverifikasi(): void
    {
        $body = $this->asDosen()->get('/arsip?q=Rahasia')->getBody();

        $this->assertStringNotContainsString('Rapat Rahasia Menunggu', $body);
        $this->assertStringContainsString('Tidak ada arsip', $body);
    }

    public function testPencarianRentangTanggal(): void
    {
        $body = $this->asDosen()->get('/arsip?dari=2026-08-01&sampai=2026-08-31')->getBody();

        $this->assertStringContainsString('Rapat Akreditasi', $body);
        $this->assertStringNotContainsString('Rapat Kurikulum', $body);
    }

    public function testKataKunciDenganKarakterLikeTidakMencocokkanSemua(): void
    {
        $body = $this->asDosen()->get('/arsip?q=' . urlencode('%'))->getBody();

        $this->assertStringContainsString('Tidak ada arsip', $body);
    }

    public function testKataKunciDenganKutipTidakMenimbulkanError(): void
    {
        $result = $this->asDosen()->get("/arsip?q=" . urlencode("O'Brien \" ! _"));

        $result->assertOK();
        $this->assertStringContainsString('Tidak ada arsip', $result->getBody());
    }

    public function testTanggalTidakValidDiabaikan(): void
    {
        $result = $this->asDosen()->get('/arsip?dari=bukan-tanggal&sampai=2026-13-45');

        $result->assertOK();
        $this->assertStringContainsString('Rapat Kurikulum', $result->getBody());
        $this->assertStringContainsString('Rapat Akreditasi', $result->getBody());
    }

    public function testDetailArsipTerverifikasiTampil(): void
    {
        $result = $this->asDosen()->get("/arsip/{$this->verifiedId}");

        $result->assertOK();
        $this->assertStringContainsString('Pembahasan mata kuliah baru', $result->getBody());
    }

    /**
     * @dataProvider tidakTerverifikasiProvider
     */
    public function testDetailArsipTidakTerverifikasiDitolak(string $property): void
    {
        $result = $this->asDosen()->get('/arsip/' . $this->{$property});

        $result->assertRedirectTo('/arsip');
        $result->assertSessionHas('error', 'Arsip tidak ditemukan.');
    }

    public static function tidakTerverifikasiProvider(): array
    {
        return ['menunggu' => ['pendingId'], 'ditolak' => ['rejectedId']];
    }

    public function testDetailArsipTidakAdaDiarahkanKeDaftar(): void
    {
        $this->asDosen()->get('/arsip/99999')->assertRedirectTo('/arsip');
    }
}
