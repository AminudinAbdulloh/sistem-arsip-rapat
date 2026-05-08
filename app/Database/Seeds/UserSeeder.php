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
                'jabatan' => 'Kepala Program Studi',
            ],
            [
                'nip' => '198502152010012002',
                'nama' => 'Dr. Siti Rahayu',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Sekretaris Prodi',
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
