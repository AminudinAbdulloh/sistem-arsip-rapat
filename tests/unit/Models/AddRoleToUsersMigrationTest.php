<?php

use App\Database\Migrations\AddRoleToUsers;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Migrasi AddRoleToUsers pada database yang sudah berisi pengguna lama:
 * semua akun lama harus menjadi admin, dan down() menghapus kolom kembali.
 *
 * @internal
 */
final class AddRoleToUsersMigrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    public function testAkunLamaMenjadiAdminSetelahMigrasi(): void
    {
        $migration = new AddRoleToUsers();
        $migration->down();
        $this->db->resetDataCache();
        $this->assertFalse($this->db->fieldExists('role', 'users'));

        $this->db->table('users')->insertBatch([
            ['nip' => '1', 'nama' => 'Lama A', 'kata_sandi' => 'x', 'jabatan' => 'Dosen'],
            ['nip' => '2', 'nama' => 'Lama B', 'kata_sandi' => 'x', 'jabatan' => 'Sekretaris'],
        ]);

        $migration->up();
        $this->db->resetDataCache();

        $this->assertTrue($this->db->fieldExists('role', 'users'));
        $this->assertSame(2, $this->db->table('users')->where('role', 'admin')->countAllResults());
    }
}
