<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterDokumentasiToText extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('notulensi_rapat', [
            'dokumentasi' => [
                'name'       => 'dokumentasi',
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('notulensi_rapat', [
            'dokumentasi' => [
                'name'       => 'dokumentasi',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }
}
