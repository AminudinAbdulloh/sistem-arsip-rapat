<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nip' => '198001012005011001',
                'nama' => 'Administrator ITD',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Administrator',
                'role' => 'admin',
            ],
            [
                'nip' => '197505102003121001',
                'nama' => 'Dr. Budi Santoso',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Kepala Program Studi',
                'role' => 'kaprodi',
            ],
            [
                'nip' => '198502152010012002',
                'nama' => 'Dr. Siti Rahayu',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Sekretaris Prodi',
                'role' => 'sekretaris',
            ],
            [
                'nip' => '199003202019031003',
                'nama' => 'Ahmad Fauzi, M.Kom.',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Dosen',
                'role' => 'dosen',
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
