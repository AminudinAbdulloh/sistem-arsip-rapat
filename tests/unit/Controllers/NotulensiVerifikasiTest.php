<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Alur verifikasi notulensi oleh Ketua Program Studi.
 *
 * @internal
 */
final class NotulensiVerifikasiTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private int $kaprodiId;
    private int $sekretarisId;
    private int $notulensiId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kaprodiId    = $this->createUser('10', 'Kaprodi Uji', 'kaprodi');
        $this->sekretarisId = $this->createUser('20', 'Sekretaris Uji', 'sekretaris');

        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'Ruang A',
            'acara' => 'Rapat Kurikulum', 'created_by' => $this->sekretarisId,
        ]);
        $undanganId = (int) $this->db->insertID();

        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => $undanganId, 'deskripsi_rapat' => 'Hasil rapat kurikulum',
            'created_by' => $this->sekretarisId,
        ]);
        $this->notulensiId = (int) $this->db->insertID();
    }

    private function createUser(string $nip, string $nama, string $role): int
    {
        $this->db->table('users')->insert([
            'nip' => $nip, 'nama' => $nama, 'jabatan' => 'X', 'role' => $role,
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
        ]);

        return (int) $this->db->insertID();
    }

    private function as(int $id, string $role)
    {
        return $this->withSession(['user' => [
            'id' => $id, 'nip' => 'x', 'nama' => 'Uji', 'jabatan' => 'X', 'foto_profil' => null, 'role' => $role,
        ]]);
    }

    private function row(): array
    {
        return $this->db->table('notulensi_rapat')->where('id', $this->notulensiId)->get()->getRowArray();
    }

    public function testNotulensiBaruBerstatusMenunggu(): void
    {
        $row = $this->row();

        $this->assertSame('menunggu', $row['status_verifikasi']);
        $this->assertNull($row['verified_by']);
        $this->assertNull($row['verified_at']);
    }

    public function testKaprodiMenyetujuiNotulensi(): void
    {
        $result = $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", [
            'aksi' => 'setujui', 'catatan' => '',
        ]);

        $result->assertRedirectTo("/notulensi/{$this->notulensiId}/show");
        $result->assertSessionHas('success', 'Notulensi berhasil diverifikasi.');
        $row = $this->row();
        $this->assertSame('terverifikasi', $row['status_verifikasi']);
        $this->assertSame((string) $this->kaprodiId, (string) $row['verified_by']);
        $this->assertNotNull($row['verified_at']);
    }

    public function testKaprodiMenolakTanpaAlasanDitolakSistem(): void
    {
        $result = $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", [
            'aksi' => 'tolak', 'catatan' => '   ',
        ]);

        $result->assertRedirectTo("/notulensi/{$this->notulensiId}/show");
        $result->assertSessionHas('error', 'Alasan penolakan wajib diisi.');
        $this->assertSame('menunggu', $this->row()['status_verifikasi']);
    }

    public function testKaprodiMenolakDenganAlasan(): void
    {
        $result = $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", [
            'aksi' => 'tolak', 'catatan' => 'Daftar keputusan belum lengkap',
        ]);

        $result->assertRedirectTo("/notulensi/{$this->notulensiId}/show");
        $row = $this->row();
        $this->assertSame('ditolak', $row['status_verifikasi']);
        $this->assertSame('Daftar keputusan belum lengkap', $row['catatan_verifikasi']);
    }

    public function testAksiTidakValidDitolak(): void
    {
        $result = $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", [
            'aksi' => 'hapus',
        ]);

        $result->assertSessionHas('error', 'Aksi verifikasi tidak valid.');
        $this->assertSame('menunggu', $this->row()['status_verifikasi']);
    }

    public function testNotulensiTidakDitemukan(): void
    {
        $result = $this->as($this->kaprodiId, 'kaprodi')->post('/notulensi/99999/verifikasi', ['aksi' => 'setujui']);

        $result->assertRedirectTo('/notulensi');
        $result->assertSessionHas('error', 'Notulensi tidak ditemukan.');
    }

    public function testPersetujuanMenghapusAlasanPenolakanSebelumnya(): void
    {
        $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", [
            'aksi' => 'tolak', 'catatan' => 'Kurang lengkap',
        ]);
        $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", [
            'aksi' => 'setujui',
        ]);

        $row = $this->row();
        $this->assertSame('terverifikasi', $row['status_verifikasi']);
        $this->assertNull($row['catatan_verifikasi']);
    }

    /**
     * @dataProvider selainKaprodiProvider
     */
    public function testSelainKaprodiTidakBolehMemverifikasi(string $role): void
    {
        $result = $this->as(99, $role)->post("/notulensi/{$this->notulensiId}/verifikasi", ['aksi' => 'setujui']);

        $result->assertRedirectTo('/dashboard');
        $this->assertSame('menunggu', $this->row()['status_verifikasi']);
    }

    public static function selainKaprodiProvider(): array
    {
        return ['admin' => ['admin'], 'sekretaris' => ['sekretaris'], 'dosen' => ['dosen']];
    }

    public function testMengubahNotulensiMengembalikanStatusKeMenunggu(): void
    {
        $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", ['aksi' => 'setujui']);
        $undanganId = (int) $this->row()['undangan_id'];

        $this->as($this->sekretarisId, 'sekretaris')->post("/notulensi/{$this->notulensiId}/update", [
            'undangan_id' => $undanganId, 'deskripsi_rapat' => 'Isi sudah direvisi', 'catatan' => '',
        ]);

        $row = $this->row();
        $this->assertSame('Isi sudah direvisi', $row['deskripsi_rapat']);
        $this->assertSame('menunggu', $row['status_verifikasi']);
        $this->assertNull($row['verified_by']);
        $this->assertNull($row['verified_at']);
        $this->assertNull($row['catatan_verifikasi']);
    }

    public function testDaftarNotulensiDapatDifilterMenurutStatus(): void
    {
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Selasa', 'waktu' => '2026-10-06 09:00:00', 'tempat' => 'Ruang B',
            'acara' => 'Rapat Anggaran', 'created_by' => $this->sekretarisId,
        ]);
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => (int) $this->db->insertID(), 'deskripsi_rapat' => 'Sudah beres',
            'created_by' => $this->sekretarisId, 'status_verifikasi' => 'terverifikasi',
        ]);

        $menunggu = $this->as($this->kaprodiId, 'kaprodi')->get('/notulensi?status=menunggu')->getBody();
        $this->assertStringContainsString('Rapat Kurikulum', $menunggu);
        $this->assertStringNotContainsString('Rapat Anggaran', $menunggu);

        $semua = $this->as($this->kaprodiId, 'kaprodi')->get('/notulensi')->getBody();
        $this->assertStringContainsString('Rapat Kurikulum', $semua);
        $this->assertStringContainsString('Rapat Anggaran', $semua);
    }

    public function testHalamanDetailMenampilkanAlasanPenolakanUntukSekretaris(): void
    {
        $this->as($this->kaprodiId, 'kaprodi')->post("/notulensi/{$this->notulensiId}/verifikasi", [
            'aksi' => 'tolak', 'catatan' => 'Lampirkan daftar hadir',
        ]);

        $body = $this->as($this->sekretarisId, 'sekretaris')->get("/notulensi/{$this->notulensiId}/show")->getBody();

        $this->assertStringContainsString('Ditolak', $body);
        $this->assertStringContainsString('Lampirkan daftar hadir', $body);
        $this->assertStringNotContainsString('name="aksi"', $body);
    }

    public function testFormVerifikasiHanyaTampilUntukKaprodi(): void
    {
        $kaprodi = $this->as($this->kaprodiId, 'kaprodi')->get("/notulensi/{$this->notulensiId}/show")->getBody();
        $admin   = $this->as(1, 'admin')->get("/notulensi/{$this->notulensiId}/show")->getBody();

        $this->assertStringContainsString('name="aksi"', $kaprodi);
        $this->assertStringNotContainsString('name="aksi"', $admin);
    }
}
