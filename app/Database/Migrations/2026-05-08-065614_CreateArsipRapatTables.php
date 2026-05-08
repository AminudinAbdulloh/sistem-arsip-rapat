<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArsipRapatTables extends Migration
{
    public function up()
    {
        // Users table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nip' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'unique' => true,
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'kata_sandi' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'foto_profil' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'jabatan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');

        // Undangan Rapat table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'hari' => [
                'type' => "ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')",
                'null' => false,
            ],
            'waktu' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'tempat' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'acara' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('undangan_rapat');

        // Notulensi Rapat table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'undangan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'tgl_rapat' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'tema_rapat' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'deskripsi_rapat' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dokumentasi' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('undangan_id', 'unique_notulensi_undangan');
        $this->forge->addForeignKey('undangan_id', 'undangan_rapat', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notulensi_rapat');
    }

    public function down()
    {
        $this->forge->dropTable('notulensi_rapat', true);
        $this->forge->dropTable('undangan_rapat', true);
        $this->forge->dropTable('users', true);
    }
}
