<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVerifikasiToNotulensi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('notulensi_rapat', [
            'status_verifikasi' => [
                'type'    => "ENUM('menunggu','terverifikasi','ditolak')",
                'null'    => false,
                'default' => 'menunggu',
            ],
            'catatan_verifikasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'verified_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->db->query(
            'ALTER TABLE notulensi_rapat ADD CONSTRAINT fk_notulensi_verified_by '
            . 'FOREIGN KEY (verified_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE notulensi_rapat DROP FOREIGN KEY fk_notulensi_verified_by');
        $this->forge->dropColumn('notulensi_rapat', ['status_verifikasi', 'catatan_verifikasi', 'verified_by', 'verified_at']);
    }
}
