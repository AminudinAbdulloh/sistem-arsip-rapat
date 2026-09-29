<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Menu dan tombol harus menyesuaikan role (penjaga sesungguhnya tetap RoleFilter).
 *
 * @internal
 */
final class RoleVisibilityTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private function page(string $role, string $uri): string
    {
        $session = ['user' => ['id' => 1, 'nip' => '1', 'nama' => 'Tester', 'jabatan' => '', 'foto_profil' => null, 'role' => $role]];

        return $this->withSession($session)->get($uri)->getBody();
    }

    public function testSidebarMenuPenggunaHanyaUntukAdmin(): void
    {
        $this->assertStringContainsString('href="/users"', $this->page('admin', '/dashboard'));
        $this->assertStringNotContainsString('href="/users"', $this->page('sekretaris', '/dashboard'));
        $this->assertStringNotContainsString('href="/users"', $this->page('kaprodi', '/dashboard'));
    }

    public function testSidebarDosenTanpaMenuUndanganDanNotulensi(): void
    {
        $body = $this->page('dosen', '/dashboard');

        $this->assertStringNotContainsString('href="/undangan"', $body);
        $this->assertStringNotContainsString('href="/notulensi"', $body);
    }

    public function testSidebarMenampilkanLabelRole(): void
    {
        $this->assertStringContainsString('Ketua Program Studi', $this->page('kaprodi', '/dashboard'));
    }

    public function testTombolTambahUndanganHanyaAdminDanSekretaris(): void
    {
        $this->assertStringContainsString('Tambah Undangan', $this->page('sekretaris', '/undangan'));
        $this->assertStringContainsString('Tambah Undangan', $this->page('admin', '/undangan'));
        $this->assertStringNotContainsString('Tambah Undangan', $this->page('kaprodi', '/undangan'));
    }

    public function testTombolTambahNotulensiHanyaAdminDanSekretaris(): void
    {
        $this->assertStringContainsString('Tambah Notulensi', $this->page('sekretaris', '/notulensi'));
        $this->assertStringNotContainsString('Tambah Notulensi', $this->page('kaprodi', '/notulensi'));
    }

    public function testTombolLaporanDashboardHanyaAdminDanKaprodi(): void
    {
        $this->assertStringContainsString('Laporan Bulanan', $this->page('kaprodi', '/dashboard'));
        $this->assertStringContainsString('Laporan Bulanan', $this->page('admin', '/dashboard'));
        $this->assertStringNotContainsString('Laporan Bulanan', $this->page('sekretaris', '/dashboard'));
        $this->assertStringNotContainsString('Laporan Bulanan', $this->page('dosen', '/dashboard'));
    }

    public function testMenuArsipRapatTampilUntukSemuaRole(): void
    {
        foreach (['admin', 'kaprodi', 'sekretaris', 'dosen'] as $role) {
            $this->assertStringContainsString('href="/arsip"', $this->page($role, '/dashboard'), $role);
        }
    }

    public function testKartuMenungguVerifikasiHanyaUntukKaprodi(): void
    {
        $this->db->table('users')->insert(['nip' => '1', 'nama' => 'S', 'kata_sandi' => 'x', 'role' => 'sekretaris']);
        $userId = (int) $this->db->insertID();
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $userId,
        ]);
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => (int) $this->db->insertID(), 'deskripsi_rapat' => 'D', 'created_by' => $userId,
        ]);

        $kaprodi = $this->page('kaprodi', '/dashboard');
        $this->assertStringContainsString('Menunggu Verifikasi', $kaprodi);
        $this->assertStringContainsString('/notulensi?status=menunggu', $kaprodi);

        $this->assertStringNotContainsString('Menunggu Verifikasi', $this->page('sekretaris', '/dashboard'));
        $this->assertStringNotContainsString('Menunggu Verifikasi', $this->page('dosen', '/dashboard'));
    }

    public function testDosenMelihatTautanCariArsipDiDashboard(): void
    {
        $this->assertStringContainsString('Cari Arsip Rapat', $this->page('dosen', '/dashboard'));
    }

    public function testLaporanMemuatStatusVerifikasi(): void
    {
        $this->db->table('users')->insert(['nip' => '1', 'nama' => 'S', 'kata_sandi' => 'x', 'role' => 'sekretaris']);
        $userId = (int) $this->db->insertID();
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'Rapat Laporan', 'created_by' => $userId,
        ]);
        // tgl_rapat sengaja tidak diisi, seperti notulensi yang dibuat lewat form sekarang
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => (int) $this->db->insertID(), 'deskripsi_rapat' => 'D', 'created_by' => $userId,
            'status_verifikasi' => 'terverifikasi',
        ]);

        $body = $this->page('kaprodi', '/dashboard/download?type=tahunan&tahun=2026');

        $this->assertStringContainsString('<th>Status</th>', $body);
        $this->assertStringContainsString('Terverifikasi', $body);
        $this->assertStringContainsString('Notulensi Terverifikasi', $body);
    }

    private function buatNotulensiTanpaTglRapat(): void
    {
        $this->db->table('users')->insert(['nip' => '1', 'nama' => 'S', 'kata_sandi' => 'x', 'role' => 'sekretaris']);
        $userId = (int) $this->db->insertID();
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'Ruang Laporan',
            'acara' => 'Rapat Anggaran', 'created_by' => $userId,
        ]);
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => (int) $this->db->insertID(), 'deskripsi_rapat' => 'Isi notulensi anggaran', 'created_by' => $userId,
        ]);
    }

    public function testLaporanMemuatNotulensiBerdasarkanTanggalUndanganBukanTglRapat(): void
    {
        $this->buatNotulensiTanpaTglRapat();

        foreach (['/dashboard/download?type=tahunan&tahun=2026', '/dashboard/download?type=bulanan&bulan=10&tahun=2026'] as $uri) {
            $body = $this->page('kaprodi', $uri);

            $this->assertStringContainsString('Isi notulensi anggaran', $body, $uri);
            $this->assertStringContainsString('05/10/2026', $body, $uri);
            $this->assertStringNotContainsString('01/01/1970', $body, $uri);
            $this->assertStringNotContainsString('<th>Tema</th>', $body, $uri);
        }
    }

    public function testLaporanBulanLainTidakMemuatNotulensiTersebut(): void
    {
        $this->buatNotulensiTanpaTglRapat();

        $body = $this->page('kaprodi', '/dashboard/download?type=bulanan&bulan=11&tahun=2026');

        $this->assertStringNotContainsString('Isi notulensi anggaran', $body);
    }

    public function testKartuDanGrafikDashboardMenghitungNotulensiBaru(): void
    {
        $this->buatNotulensiTanpaTglRapat();

        $body = $this->page('kaprodi', '/dashboard?bulan=10&tahun=2026');

        $this->assertMatchesRegularExpression('/Notulensi Bulan Ini<\/p>\s*<p[^>]*>1<\/p>/', $body);
        $this->assertMatchesRegularExpression('/Total Notulensi Tahun 2026<\/p>\s*<p[^>]*>1<\/p>/', $body);
        $this->assertStringContainsString('{"bulan":"Okt","undangan":1,"notulensi":1}', $body);
    }

    public function testDosenTidakMelihatGrafikDashboard(): void
    {
        $this->assertStringNotContainsString('chartArsip', $this->page('dosen', '/dashboard'));
        $this->assertStringContainsString('chartArsip', $this->page('sekretaris', '/dashboard'));
    }
}
