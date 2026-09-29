<?php

use App\Database\Migrations\AddVerifikasiToNotulensi;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Migrasi AddVerifikasiToNotulensi pada database yang sudah berisi notulensi lama:
 * notulensi lama berstatus "menunggu", dan FK verified_by bersifat SET NULL.
 *
 * @internal
 */
final class AddVerifikasiToNotulensiMigrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    public function testNotulensiLamaBerstatusMenunggu(): void
    {
        $migration = new AddVerifikasiToNotulensi();
        $migration->down();

        $this->db->table('users')->insert(['nip' => '1', 'nama' => 'S', 'kata_sandi' => 'x', 'role' => 'sekretaris']);
        $userId = (int) $this->db->insertID();
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $userId,
        ]);
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => (int) $this->db->insertID(), 'deskripsi_rapat' => 'D', 'created_by' => $userId,
        ]);

        $migration->up();

        $row = $this->db->table('notulensi_rapat')->get()->getRowArray();
        $this->assertSame('menunggu', $row['status_verifikasi']);
        $this->assertNull($row['verified_by']);
    }

    public function testMenghapusPemverifikasiMengosongkanVerifiedByTanpaMenghapusNotulensi(): void
    {
        $this->db->table('users')->insertBatch([
            ['nip' => '1', 'nama' => 'Sekretaris', 'kata_sandi' => 'x', 'role' => 'sekretaris'],
            ['nip' => '2', 'nama' => 'Kaprodi', 'kata_sandi' => 'x', 'role' => 'kaprodi'],
        ]);
        $sekretaris = (int) $this->db->table('users')->where('nip', '1')->get()->getRow()->id;
        $kaprodi    = (int) $this->db->table('users')->where('nip', '2')->get()->getRow()->id;

        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $sekretaris,
        ]);
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => (int) $this->db->insertID(), 'deskripsi_rapat' => 'D', 'created_by' => $sekretaris,
            'status_verifikasi' => 'terverifikasi', 'verified_by' => $kaprodi, 'verified_at' => '2026-10-06 10:00:00',
        ]);

        $this->db->table('users')->where('id', $kaprodi)->delete();

        $row = $this->db->table('notulensi_rapat')->get()->getRowArray();
        $this->assertNotNull($row, 'notulensi tidak boleh ikut terhapus');
        $this->assertNull($row['verified_by']);
        $this->assertSame('terverifikasi', $row['status_verifikasi']);
    }
}
