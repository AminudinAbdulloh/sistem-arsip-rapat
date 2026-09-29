<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pengujian White-Box terhadap AuthController::login().
 *
 * Method login() memiliki empat jalur eksekusi (branch) yang diuji satu per satu:
 *  1. NIP dan/atau kata sandi kosong        -> redirect /login, pesan "wajib diisi"
 *  2. NIP tidak ditemukan di database       -> redirect /login, pesan "salah"
 *  3. NIP ditemukan tetapi kata sandi salah -> redirect /login, pesan "salah"
 *  4. NIP dan kata sandi benar              -> session dibuat, redirect /dashboard
 *
 * @internal
 */
final class AuthControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

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

    public function testJalur1FieldKosongDitolakDenganPesanWajibDiisi(): void
    {
        $result = $this->withSession()->post('/login', [
            'nip'        => '   ',
            'kata_sandi' => '   ',
        ]);

        $result->assertRedirectTo('/login');
        $result->assertSessionHas('error', 'NIP dan kata sandi wajib diisi.');
        $result->assertSessionMissing('user');
    }

    public function testJalur2NipTidakDitemukanDitolakDenganPesanSalah(): void
    {
        $result = $this->withSession()->post('/login', [
            'nip'        => '999999999999999999',
            'kata_sandi' => 'password',
        ]);

        $result->assertRedirectTo('/login');
        $result->assertSessionHas('error', 'NIP atau kata sandi salah.');
        $result->assertSessionMissing('user');
    }

    public function testJalur3KataSandiSalahDitolakDenganPesanSalah(): void
    {
        $result = $this->withSession()->post('/login', [
            'nip'        => '198001012005011001',
            'kata_sandi' => 'kata-sandi-salah',
        ]);

        $result->assertRedirectTo('/login');
        $result->assertSessionHas('error', 'NIP atau kata sandi salah.');
        $result->assertSessionMissing('user');
    }

    public function testJalur4KredensialBenarMembuatSessionDanRedirectDashboard(): void
    {
        $result = $this->withSession()->post('/login', [
            'nip'        => '198001012005011001',
            'kata_sandi' => 'password',
        ]);

        $result->assertRedirectTo('/dashboard');
        $result->assertSessionHas('user');
    }
}
