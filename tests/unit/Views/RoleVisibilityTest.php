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

    public function testDosenTidakMelihatGrafikDashboard(): void
    {
        $this->assertStringNotContainsString('chartArsip', $this->page('dosen', '/dashboard'));
        $this->assertStringContainsString('chartArsip', $this->page('sekretaris', '/dashboard'));
    }
}
