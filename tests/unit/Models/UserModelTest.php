<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Pengujian White-Box (unit) untuk App\Models\UserModel::findByNip().
 * Menguji dua jalur (branch) hasil query: NIP ditemukan dan NIP tidak ditemukan.
 *
 * @internal
 */
final class UserModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('users')->insert([
            'nip'        => '198001012005011001',
            'nama'       => 'Administrator ITD',
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
            'jabatan'    => 'Kepala Program Studi',
        ]);
    }

    public function testFindByNipMengembalikanUserSaatNipDitemukan(): void
    {
        $model = new UserModel();

        $user = $model->findByNip('198001012005011001');

        $this->assertIsArray($user);
        $this->assertSame('Administrator ITD', $user['nama']);
        $this->assertSame('Kepala Program Studi', $user['jabatan']);
    }

    public function testFindByNipMengembalikanNullSaatNipTidakDitemukan(): void
    {
        $model = new UserModel();

        $user = $model->findByNip('000000000000000000');

        $this->assertNull($user);
    }
}
