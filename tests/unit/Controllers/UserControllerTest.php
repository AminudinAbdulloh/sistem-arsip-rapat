<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pengujian manajemen pengguna oleh Admin, termasuk tiga pengaman:
 * tidak hapus/ubah role diri sendiri, tidak hapus/turunkan admin terakhir,
 * tidak hapus pengguna yang sudah punya data.
 *
 * @internal
 */
final class UserControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminId = $this->createUser('100', 'Admin Uji', 'admin');
    }

    private function createUser(string $nip, string $nama, string $role): int
    {
        $this->db->table('users')->insert([
            'nip' => $nip, 'nama' => $nama, 'jabatan' => 'X', 'role' => $role,
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
        ]);

        return (int) $this->db->insertID();
    }

    private function sessionFor(int $id, string $role = 'admin'): array
    {
        return ['user' => ['id' => $id, 'nip' => '100', 'nama' => 'Admin Uji', 'jabatan' => 'X', 'foto_profil' => null, 'role' => $role]];
    }

    private function asAdmin()
    {
        return $this->withSession($this->sessionFor($this->adminId));
    }

    public function testIndexMenampilkanDaftarPengguna(): void
    {
        $result = $this->asAdmin()->get('/users');

        $result->assertOK();
        $result->assertSee('Admin Uji');
    }

    public function testNonAdminDitolak(): void
    {
        $result = $this->withSession($this->sessionFor(5, 'sekretaris'))->get('/users');

        $result->assertRedirectTo('/dashboard');
    }

    public function testFormTambahDanEditTampil(): void
    {
        $id = $this->createUser('300', 'Lain', 'dosen');

        $this->asAdmin()->get('/users/create')->assertOK();
        $this->asAdmin()->get("/users/{$id}/edit")->assertOK();
        $this->asAdmin()->get("/users/{$this->adminId}/edit")->assertOK();
    }

    public function testStoreDataValidDisimpanDenganPasswordTerhash(): void
    {
        $result = $this->asAdmin()->post('/users/store', [
            'nip' => '200', 'nama' => 'Baru', 'jabatan' => 'Dosen', 'role' => 'dosen', 'kata_sandi' => 'rahasia123',
        ]);

        $result->assertRedirectTo('/users');
        $this->seeInDatabase('users', ['nip' => '200', 'role' => 'dosen']);
        $row = $this->db->table('users')->where('nip', '200')->get()->getRowArray();
        $this->assertTrue(password_verify('rahasia123', $row['kata_sandi']));
    }

    public function testStoreDataTidakValidDitolak(): void
    {
        $result = $this->asAdmin()->post('/users/store', [
            'nip' => '100', 'nama' => '', 'role' => 'hacker', 'kata_sandi' => 'pendek',
        ]);

        $result->assertRedirectTo('/users/create');
        $result->assertSessionHas('error');
        $this->assertSame(1, $this->db->table('users')->countAllResults());
    }

    public function testUpdateMengubahRolePenggunaLain(): void
    {
        $id = $this->createUser('300', 'Lain', 'dosen');

        $result = $this->asAdmin()->post("/users/{$id}/update", [
            'nip' => '300', 'nama' => 'Lain', 'jabatan' => 'X', 'role' => 'kaprodi', 'kata_sandi' => '',
        ]);

        $result->assertRedirectTo('/users');
        $this->seeInDatabase('users', ['id' => $id, 'role' => 'kaprodi']);
    }

    public function testUpdateKataSandiKosongTidakMengubahHash(): void
    {
        $id   = $this->createUser('300', 'Lain', 'dosen');
        $hash = $this->db->table('users')->where('id', $id)->get()->getRowArray()['kata_sandi'];

        $this->asAdmin()->post("/users/{$id}/update", [
            'nip' => '300', 'nama' => 'Lain Baru', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => '',
        ]);

        $row = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        $this->assertSame('Lain Baru', $row['nama']);
        $this->assertSame($hash, $row['kata_sandi']);
    }

    public function testUpdateKataSandiBaruDiganti(): void
    {
        $id = $this->createUser('300', 'Lain', 'dosen');

        $this->asAdmin()->post("/users/{$id}/update", [
            'nip' => '300', 'nama' => 'Lain', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => 'kataSandiBaru1',
        ]);

        $row = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        $this->assertTrue(password_verify('kataSandiBaru1', $row['kata_sandi']));
    }

    public function testUpdateRoleAkunSendiriDitolak(): void
    {
        $result = $this->asAdmin()->post("/users/{$this->adminId}/update", [
            'nip' => '100', 'nama' => 'Admin Uji', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => '',
        ]);

        $result->assertSessionHas('error', 'Anda tidak dapat mengubah role akun sendiri.');
        $this->seeInDatabase('users', ['id' => $this->adminId, 'role' => 'admin']);
    }

    public function testUpdateMenurunkanAdminTerakhirDitolak(): void
    {
        // Session milik admin lain yang tidak ada di DB (mis. session usang).
        $result = $this->withSession($this->sessionFor(999))->post("/users/{$this->adminId}/update", [
            'nip' => '100', 'nama' => 'Admin Uji', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => '',
        ]);

        $result->assertSessionHas('error', 'Admin terakhir tidak dapat diturunkan rolenya.');
        $this->seeInDatabase('users', ['id' => $this->adminId, 'role' => 'admin']);
    }

    public function testDeleteAkunSendiriDitolak(): void
    {
        $this->createUser('300', 'Admin Kedua', 'admin');

        $result = $this->asAdmin()->post("/users/{$this->adminId}/delete");

        $result->assertSessionHas('error', 'Anda tidak dapat menghapus akun sendiri.');
        $this->seeInDatabase('users', ['id' => $this->adminId]);
    }

    public function testDeleteAdminTerakhirDitolak(): void
    {
        $result = $this->withSession($this->sessionFor(999))->post("/users/{$this->adminId}/delete");

        $result->assertSessionHas('error', 'Admin terakhir tidak dapat dihapus.');
        $this->seeInDatabase('users', ['id' => $this->adminId]);
    }

    public function testDeletePenggunaYangPunyaDataDitolak(): void
    {
        $id = $this->createUser('300', 'Sekretaris', 'sekretaris');
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $id,
        ]);

        $result = $this->asAdmin()->post("/users/{$id}/delete");

        $result->assertSessionHas('error', 'Pengguna tidak dapat dihapus karena sudah memiliki data undangan/notulensi.');
        $this->seeInDatabase('users', ['id' => $id]);
        $this->assertSame(1, $this->db->table('undangan_rapat')->countAllResults());
    }

    public function testDeletePenggunaBiasaBerhasil(): void
    {
        $id = $this->createUser('300', 'Dosen', 'dosen');

        $result = $this->asAdmin()->post("/users/{$id}/delete");

        $result->assertRedirectTo('/users');
        $result->assertSessionHas('success', 'Pengguna berhasil dihapus.');
        $this->dontSeeInDatabase('users', ['id' => $id]);
    }
}
