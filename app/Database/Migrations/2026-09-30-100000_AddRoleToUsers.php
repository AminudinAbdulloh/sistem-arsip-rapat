<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role' => [
                'type'    => "ENUM('admin','kaprodi','sekretaris','dosen')",
                'null'    => false,
                'default' => 'dosen',
                'after'   => 'jabatan',
            ],
        ]);

        // Akun yang sudah ada dijadikan admin agar tidak ada yang terkunci.
        $this->db->table('users')->update(['role' => 'admin']);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role');
    }
}
