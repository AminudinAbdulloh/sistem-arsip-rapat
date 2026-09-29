<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pengujian matriks hak akses: role x route.
 *
 * @internal
 */
final class RoleFilterTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private const PESAN = 'Anda tidak memiliki akses ke halaman tersebut.';

    public static function matriksProvider(): array
    {
        return [
            // route tulis undangan (admin, sekretaris)
            'admin tulis undangan'      => ['admin', '/undangan/create', true],
            'sekretaris tulis undangan' => ['sekretaris', '/undangan/create', true],
            'kaprodi tulis undangan'    => ['kaprodi', '/undangan/create', false],
            'dosen tulis undangan'      => ['dosen', '/undangan/create', false],
            // route tulis notulensi
            'sekretaris tulis notulensi' => ['sekretaris', '/notulensi/create', true],
            'kaprodi tulis notulensi'    => ['kaprodi', '/notulensi/create', false],
            // route baca (admin, sekretaris, kaprodi)
            'kaprodi baca undangan'  => ['kaprodi', '/undangan', true],
            'dosen baca undangan'    => ['dosen', '/undangan', false],
            'kaprodi baca notulensi' => ['kaprodi', '/notulensi', true],
            'dosen baca notulensi'   => ['dosen', '/notulensi', false],
            // laporan (admin, kaprodi)
            'admin laporan'      => ['admin', '/dashboard/download?type=tahunan&tahun=2026', true],
            'kaprodi laporan'    => ['kaprodi', '/dashboard/download?type=tahunan&tahun=2026', true],
            'sekretaris laporan' => ['sekretaris', '/dashboard/download?type=tahunan&tahun=2026', false],
            'dosen laporan'      => ['dosen', '/dashboard/download?type=tahunan&tahun=2026', false],
            // dashboard: semua role
            'dosen dashboard' => ['dosen', '/dashboard', true],
        ];
    }

    /**
     * @dataProvider matriksProvider
     */
    public function testMatriksAkses(string $role, string $uri, bool $diizinkan): void
    {
        $result = $this->withSession($this->sessionFor($role))->get($uri);

        if ($diizinkan) {
            $result->assertOK();
        } else {
            $result->assertRedirectTo('/dashboard');
            $result->assertSessionHas('error', self::PESAN);
        }
    }

    public function testBelumLoginDiarahkanKeLogin(): void
    {
        $result = $this->get('/undangan');

        $result->assertRedirectTo('/login');
    }

    public function testRouteTulisMenolakPostDariKaprodi(): void
    {
        $result = $this->withSession($this->sessionFor('kaprodi'))->post('/undangan/store', [
            'hari' => 'Senin', 'waktu' => '2026-10-05T09:00', 'tempat' => 'R', 'acara' => 'A',
        ]);

        $result->assertRedirectTo('/dashboard');
        $this->assertSame(0, $this->db->table('undangan_rapat')->countAllResults());
    }

    public function testHelperHasRole(): void
    {
        session()->set($this->sessionFor('sekretaris'));

        $this->assertTrue(has_role('admin', 'sekretaris'));
        $this->assertFalse(has_role('admin', 'kaprodi'));
    }

    private function sessionFor(string $role): array
    {
        return ['user' => ['id' => 1, 'nip' => '1', 'nama' => 'Tester', 'jabatan' => '', 'foto_profil' => null, 'role' => $role]];
    }
}
