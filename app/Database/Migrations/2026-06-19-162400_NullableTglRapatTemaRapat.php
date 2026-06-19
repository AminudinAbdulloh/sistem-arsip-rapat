<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NullableTglRapatTemaRapat extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('notulensi_rapat', [
            'tgl_rapat' => [
                'name' => 'tgl_rapat',
                'type' => 'DATE',
                'null' => true,
            ],
            'tema_rapat' => [
                'name'       => 'tema_rapat',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('notulensi_rapat', [
            'tgl_rapat' => [
                'name' => 'tgl_rapat',
                'type' => 'DATE',
                'null' => false,
            ],
            'tema_rapat' => [
                'name'       => 'tema_rapat',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);
    }
}
