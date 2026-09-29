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

    public function testRoleDefaultDosenSaatTidakDiisi(): void
    {
        $user = (new UserModel())->findByNip('198001012005011001');

        $this->assertSame('dosen', $user['role']);
    }

    public function testCountByRoleMenghitungPerRole(): void
    {
        $this->db->table('users')->insert([
            'nip' => '111', 'nama' => 'A', 'kata_sandi' => 'x', 'role' => 'admin',
        ]);
        $model = new UserModel();

        $this->assertSame(1, $model->countByRole('admin'));
        $this->assertSame(1, $model->countByRole('dosen'));
        $this->assertSame(0, $model->countByRole('kaprodi'));
    }

    public function testRulesForMenolakRoleTidakValidDanKataSandiPendek(): void
    {
        $validation = service('validation');
        $validation->setRules((new UserModel())->rulesFor(null, true));

        $ok = $validation->run([
            'nip' => '222', 'nama' => 'B', 'jabatan' => '', 'role' => 'hacker', 'kata_sandi' => 'short',
        ]);

        $this->assertFalse($ok);
        $this->assertArrayHasKey('role', $validation->getErrors());
        $this->assertArrayHasKey('kata_sandi', $validation->getErrors());
    }

    public function testRulesForMenolakNipDuplikatKecualiMilikSendiri(): void
    {
        $model = new UserModel();
        $ownId = (int) $model->findByNip('198001012005011001')['id'];
        $input = ['nip' => '198001012005011001', 'nama' => 'A', 'jabatan' => '', 'role' => 'dosen', 'kata_sandi' => ''];

        $validation = service('validation');
        $validation->setRules($model->rulesFor(null, false));
        $this->assertFalse($validation->run($input));
        $this->assertArrayHasKey('nip', $validation->getErrors());

        $validation->reset();
        $validation->setRules($model->rulesFor($ownId, false));
        $this->assertTrue($validation->run($input));
    }

    public function testHasRelatedDataTrueBilaPenggunaPunyaUndangan(): void
    {
        $model = new UserModel();
        $id    = (int) $model->findByNip('198001012005011001')['id'];
        $this->assertFalse($model->hasRelatedData($id));

        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $id,
        ]);

        $this->assertTrue($model->hasRelatedData($id));
    }

    public function testSeederMembuatSatuAkunPerRole(): void
    {
        $this->db->table('users')->where('nip', '198001012005011001')->delete();

        (new \App\Database\Seeds\UserSeeder(config('Database')))->run();

        $model = new UserModel();
        foreach (UserModel::ROLES as $role) {
            $this->assertSame(1, $model->countByRole($role), "role {$role}");
        }
    }
}
