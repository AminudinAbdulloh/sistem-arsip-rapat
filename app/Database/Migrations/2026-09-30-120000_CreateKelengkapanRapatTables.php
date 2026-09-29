<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKelengkapanRapatTables extends Migration
{
    private function idField(): array
    {
        return ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true];
    }

    private function fkField(bool $null = false): array
    {
        return ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => $null];
    }

    private function timestamps(): array
    {
        return [
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ];
    }

    public function up()
    {
        // Daftar hadir: banyak peserta per undangan
        $this->forge->addField(array_merge([
            'id'          => $this->idField(),
            'undangan_id' => $this->fkField(),
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'jabatan'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status'      => ['type' => "ENUM('hadir','izin','tidak_hadir')", 'null' => false, 'default' => 'hadir'],
            'keterangan'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ], $this->timestamps()));
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('undangan_id', 'undangan_rapat', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('daftar_hadir');

        // Berita acara: satu per undangan
        $this->forge->addField(array_merge([
            'id'          => $this->idField(),
            'undangan_id' => $this->fkField(),
            'nomor'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'uraian'      => ['type' => 'TEXT', 'null' => false],
            'keputusan'   => ['type' => 'TEXT', 'null' => true],
            'created_by'  => $this->fkField(),
        ], $this->timestamps()));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('undangan_id', 'unique_berita_acara_undangan');
        $this->forge->addForeignKey('undangan_id', 'undangan_rapat', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('berita_acara');

        // Dokumen rapat: berkas disimpan di writable/uploads/dokumen (di luar web root)
        $this->forge->addField(array_merge([
            'id'          => $this->idField(),
            'undangan_id' => $this->fkField(),
            'judul'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'berkas'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'nama_asli'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'ukuran'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false, 'default' => 0],
            'mime'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'uploaded_by' => $this->fkField(),
        ], $this->timestamps()));
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('undangan_id', 'undangan_rapat', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('dokumen_rapat');
    }

    public function down()
    {
        $this->forge->dropTable('dokumen_rapat', true);
        $this->forge->dropTable('berita_acara', true);
        $this->forge->dropTable('daftar_hadir', true);
    }
}
