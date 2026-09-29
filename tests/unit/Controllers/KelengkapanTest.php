<?php

use App\Libraries\DokumenRapatService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Kelengkapan rapat: daftar hadir, berita acara, dan dokumen per undangan.
 *
 * @internal
 */
final class KelengkapanTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private int $userId;
    private int $undanganId;

    /** @var list<string> berkas fisik yang dibuat test, dibersihkan di tearDown */
    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('users')->insert([
            'nip' => '10', 'nama' => 'Sekretaris Uji', 'jabatan' => 'X', 'role' => 'sekretaris',
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
        ]);
        $this->userId = (int) $this->db->insertID();

        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'Ruang A',
            'acara' => 'Rapat Kurikulum', 'created_by' => $this->userId,
        ]);
        $this->undanganId = (int) $this->db->insertID();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function as(string $role)
    {
        return $this->withSession(['user' => [
            'id' => $this->userId, 'nip' => 'x', 'nama' => 'Uji', 'jabatan' => 'X', 'foto_profil' => null, 'role' => $role,
        ]]);
    }

    private function createDokumen(string $judul = 'Materi Rapat'): array
    {
        $berkas = 'uji_' . bin2hex(random_bytes(6)) . '.pdf';
        $dir    = WRITEPATH . 'uploads/dokumen/';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($dir . $berkas, 'isi dokumen uji');
        $this->createdFiles[] = $dir . $berkas;

        $this->db->table('dokumen_rapat')->insert([
            'undangan_id' => $this->undanganId, 'judul' => $judul, 'berkas' => $berkas,
            'nama_asli' => 'materi.pdf', 'ukuran' => 15, 'mime' => 'application/pdf', 'uploaded_by' => $this->userId,
        ]);

        return ['id' => (int) $this->db->insertID(), 'berkas' => $berkas, 'path' => $dir . $berkas];
    }

    private function verifyNotulensi(string $status = 'terverifikasi'): void
    {
        $this->db->table('notulensi_rapat')->insert([
            'undangan_id' => $this->undanganId, 'deskripsi_rapat' => 'Hasil', 'created_by' => $this->userId,
            'status_verifikasi' => $status,
        ]);
    }

    // ---------------------------------------------------------------- akses halaman

    public static function aksesHalamanProvider(): array
    {
        return ['admin' => ['admin', true], 'sekretaris' => ['sekretaris', true], 'kaprodi' => ['kaprodi', true], 'dosen' => ['dosen', false]];
    }

    /**
     * @dataProvider aksesHalamanProvider
     */
    public function testAksesHalamanKelengkapan(string $role, bool $diizinkan): void
    {
        $result = $this->as($role)->get("/undangan/{$this->undanganId}/kelengkapan");

        if ($diizinkan) {
            $result->assertOK();
            $result->assertSee('Rapat Kurikulum');
        } else {
            $result->assertRedirectTo('/dashboard');
        }
    }

    public function testUndanganTidakAdaDiarahkanKeDaftarUndangan(): void
    {
        $result = $this->as('sekretaris')->get('/undangan/99999/kelengkapan');

        $result->assertRedirectTo('/undangan');
        $result->assertSessionHas('error', 'Undangan tidak ditemukan.');
    }

    public function testFormInputHanyaTampilUntukAdminDanSekretaris(): void
    {
        $sekretaris = $this->as('sekretaris')->get("/undangan/{$this->undanganId}/kelengkapan")->getBody();
        $kaprodi    = $this->as('kaprodi')->get("/undangan/{$this->undanganId}/kelengkapan")->getBody();

        $this->assertStringContainsString("/undangan/{$this->undanganId}/hadir/store", $sekretaris);
        $this->assertStringContainsString("/undangan/{$this->undanganId}/berita-acara/save", $sekretaris);
        $this->assertStringContainsString("/undangan/{$this->undanganId}/dokumen/store", $sekretaris);
        $this->assertStringNotContainsString('/hadir/store', $kaprodi);
        $this->assertStringNotContainsString('/berita-acara/save', $kaprodi);
        $this->assertStringNotContainsString('/dokumen/store', $kaprodi);
    }

    // ---------------------------------------------------------------- daftar hadir

    public function testTambahDaftarHadirValid(): void
    {
        $result = $this->as('sekretaris')->post("/undangan/{$this->undanganId}/hadir/store", [
            'nama' => 'Dr. Budi', 'jabatan' => 'Dosen', 'status' => 'hadir', 'keterangan' => '',
        ]);

        $result->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");
        $result->assertSessionHas('success', 'Peserta berhasil ditambahkan ke daftar hadir.');
        $this->seeInDatabase('daftar_hadir', ['undangan_id' => $this->undanganId, 'nama' => 'Dr. Budi', 'status' => 'hadir']);
    }

    public function testTambahDaftarHadirTidakValidDitolak(): void
    {
        $result = $this->as('sekretaris')->post("/undangan/{$this->undanganId}/hadir/store", [
            'nama' => '  ', 'status' => 'kabur',
        ]);

        $result->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");
        $result->assertSessionHas('error');
        $this->assertSame(0, $this->db->table('daftar_hadir')->countAllResults());
    }

    public function testHapusDaftarHadir(): void
    {
        $this->db->table('daftar_hadir')->insert(['undangan_id' => $this->undanganId, 'nama' => 'Peserta', 'status' => 'izin']);
        $id = (int) $this->db->insertID();

        $result = $this->as('sekretaris')->post("/hadir/{$id}/delete");

        $result->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");
        $this->dontSeeInDatabase('daftar_hadir', ['id' => $id]);
    }

    public function testKaprodiTidakBolehMengubahDaftarHadir(): void
    {
        $this->db->table('daftar_hadir')->insert(['undangan_id' => $this->undanganId, 'nama' => 'Peserta', 'status' => 'hadir']);
        $id = (int) $this->db->insertID();

        $this->as('kaprodi')->post("/undangan/{$this->undanganId}/hadir/store", ['nama' => 'Baru', 'status' => 'hadir'])
            ->assertRedirectTo('/dashboard');
        $this->as('kaprodi')->post("/hadir/{$id}/delete")->assertRedirectTo('/dashboard');

        $this->assertSame(1, $this->db->table('daftar_hadir')->countAllResults());
    }

    // ---------------------------------------------------------------- berita acara

    public function testSimpanBeritaAcaraMembuatLaluMemperbaruiSatuRecord(): void
    {
        $this->as('sekretaris')->post("/undangan/{$this->undanganId}/berita-acara/save", [
            'nomor' => '01/BA/2026', 'uraian' => 'Rapat dibuka pukul 09.00', 'keputusan' => 'Kurikulum disetujui',
        ])->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");

        $this->as('admin')->post("/undangan/{$this->undanganId}/berita-acara/save", [
            'nomor' => '01/BA/2026', 'uraian' => 'Uraian direvisi', 'keputusan' => '',
        ]);

        $this->assertSame(1, $this->db->table('berita_acara')->countAllResults());
        $row = $this->db->table('berita_acara')->get()->getRowArray();
        $this->assertSame('Uraian direvisi', $row['uraian']);
        $this->assertSame((string) $this->userId, (string) $row['created_by']);
    }

    public function testBeritaAcaraTanpaUraianDitolak(): void
    {
        $result = $this->as('sekretaris')->post("/undangan/{$this->undanganId}/berita-acara/save", ['nomor' => 'X', 'uraian' => '  ']);

        $result->assertSessionHas('error');
        $this->assertSame(0, $this->db->table('berita_acara')->countAllResults());
    }

    public function testKaprodiTidakBolehMenyimpanBeritaAcara(): void
    {
        $result = $this->as('kaprodi')->post("/undangan/{$this->undanganId}/berita-acara/save", ['uraian' => 'Isi']);

        $result->assertRedirectTo('/dashboard');
        $this->assertSame(0, $this->db->table('berita_acara')->countAllResults());
    }

    public function testCetakBeritaAcaraMemuatUraianDanDaftarHadir(): void
    {
        $this->db->table('berita_acara')->insert([
            'undangan_id' => $this->undanganId, 'nomor' => '01/BA/2026', 'uraian' => 'Rapat dibuka',
            'keputusan' => 'Disetujui bersama', 'created_by' => $this->userId,
        ]);
        $this->db->table('daftar_hadir')->insert(['undangan_id' => $this->undanganId, 'nama' => 'Ibu Siti', 'status' => 'hadir']);

        $result = $this->as('kaprodi')->get("/undangan/{$this->undanganId}/berita-acara/cetak");

        $result->assertOK();
        $body = $result->getBody();
        $this->assertStringContainsString('BERITA ACARA', $body);
        $this->assertStringContainsString('01/BA/2026', $body);
        $this->assertStringContainsString('Rapat dibuka', $body);
        $this->assertStringContainsString('Disetujui bersama', $body);
        $this->assertStringContainsString('Ibu Siti', $body);
    }

    public function testCetakBeritaAcaraMelakukanEscapeHtml(): void
    {
        $this->db->table('berita_acara')->insert([
            'undangan_id' => $this->undanganId, 'uraian' => '<script>alert(1)</script>', 'created_by' => $this->userId,
        ]);

        $body = $this->as('sekretaris')->get("/undangan/{$this->undanganId}/berita-acara/cetak")->getBody();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $body);
        $this->assertStringContainsString('&lt;script&gt;', $body);
    }

    public function testCetakBeritaAcaraYangBelumDibuatDiarahkanKembali(): void
    {
        $result = $this->as('sekretaris')->get("/undangan/{$this->undanganId}/berita-acara/cetak");

        $result->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");
        $result->assertSessionHas('error', 'Berita acara belum dibuat.');
    }

    public function testDosenTidakBolehMencetakBeritaAcara(): void
    {
        $this->as('dosen')->get("/undangan/{$this->undanganId}/berita-acara/cetak")->assertRedirectTo('/dashboard');
    }

    // ---------------------------------------------------------------- dokumen

    public function testUploadTanpaBerkasDitolak(): void
    {
        $result = $this->as('sekretaris')->post("/undangan/{$this->undanganId}/dokumen/store", ['judul' => 'Tanpa file']);

        $result->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");
        $result->assertSessionHas('error', 'Berkas wajib dipilih dan harus berhasil diunggah.');
        $this->assertSame(0, $this->db->table('dokumen_rapat')->countAllResults());
    }

    public function testKaprodiTidakBolehMengunggahDokumen(): void
    {
        $this->as('kaprodi')->post("/undangan/{$this->undanganId}/dokumen/store", ['judul' => 'X'])->assertRedirectTo('/dashboard');
    }

    public function testHapusDokumenMenghapusRecordDanBerkasFisik(): void
    {
        $dok = $this->createDokumen();

        $result = $this->as('sekretaris')->post("/dokumen/{$dok['id']}/delete");

        $result->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");
        $this->dontSeeInDatabase('dokumen_rapat', ['id' => $dok['id']]);
        $this->assertFileDoesNotExist($dok['path']);
    }

    public function testKaprodiTidakBolehMenghapusDokumen(): void
    {
        $dok = $this->createDokumen();

        $this->as('kaprodi')->post("/dokumen/{$dok['id']}/delete")->assertRedirectTo('/dashboard');

        $this->seeInDatabase('dokumen_rapat', ['id' => $dok['id']]);
        $this->assertFileExists($dok['path']);
    }

    public function testStafDapatMengunduhDokumenMeskipBelumTerverifikasi(): void
    {
        $dok = $this->createDokumen();

        foreach (['admin', 'sekretaris', 'kaprodi'] as $role) {
            $response = $this->as($role)->get("/dokumen/{$dok['id']}/download")->response();
            $this->assertDownloadMateriPdf($response, $role);
        }
    }

    public function testDosenTidakBolehMengunduhDokumenRapatYangBelumTerverifikasi(): void
    {
        $dok = $this->createDokumen();

        $result = $this->as('dosen')->get("/dokumen/{$dok['id']}/download");

        $result->assertRedirectTo('/arsip');
        $result->assertSessionHas('error', 'Dokumen tidak tersedia.');
    }

    public function testDosenTidakBolehMengunduhDokumenBilaNotulensiDitolakAtauMenunggu(): void
    {
        $dok = $this->createDokumen();
        $this->verifyNotulensi('ditolak');

        $this->as('dosen')->get("/dokumen/{$dok['id']}/download")->assertRedirectTo('/arsip');
    }

    public function testDosenDapatMengunduhDokumenRapatYangTerverifikasi(): void
    {
        $dok = $this->createDokumen();
        $this->verifyNotulensi();

        $response = $this->as('dosen')->get("/dokumen/{$dok['id']}/download")->response();

        $this->assertDownloadMateriPdf($response, 'dosen');
    }

    /**
     * DownloadResponse membentuk header dan mengirim isi berkas hanya saat send(),
     * jadi di test header dibentuk lewat buildHeaders() dan ukuran isi dicek langsung.
     */
    private function assertDownloadMateriPdf($response, string $pesan): void
    {
        $this->assertInstanceOf(\CodeIgniter\HTTP\DownloadResponse::class, $response, $pesan);
        $this->assertSame(200, $response->getStatusCode(), $pesan);
        $response->buildHeaders();
        $this->assertStringContainsString('attachment', $response->getHeaderLine('Content-Disposition'), $pesan);
        $this->assertStringContainsString('materi.pdf', $response->getHeaderLine('Content-Disposition'), $pesan);
        $this->assertSame(strlen('isi dokumen uji'), $response->getContentLength(), $pesan);
    }

    public function testBelumLoginTidakBolehMengunduh(): void
    {
        $dok = $this->createDokumen();

        $this->get("/dokumen/{$dok['id']}/download")->assertRedirectTo('/login');
    }

    public function testUnduhDokumenYangTidakAdaDiarahkanKembali(): void
    {
        $this->as('sekretaris')->get('/dokumen/99999/download')->assertRedirectTo('/dashboard');
    }

    public function testUnduhBerkasYangHilangDariServerDiarahkanKembali(): void
    {
        $dok = $this->createDokumen();
        unlink($dok['path']);

        $result = $this->as('sekretaris')->get("/dokumen/{$dok['id']}/download");

        $result->assertRedirectTo("/undangan/{$this->undanganId}/kelengkapan");
        $result->assertSessionHas('error', 'Berkas tidak ditemukan di server.');
    }

    // ---------------------------------------------------------------- hapus undangan

    public function testMenghapusUndanganMembersihkanKelengkapanDanBerkasFisik(): void
    {
        $dok = $this->createDokumen();
        $this->db->table('daftar_hadir')->insert(['undangan_id' => $this->undanganId, 'nama' => 'P', 'status' => 'hadir']);
        $this->db->table('berita_acara')->insert(['undangan_id' => $this->undanganId, 'uraian' => 'U', 'created_by' => $this->userId]);

        $this->as('sekretaris')->post("/undangan/{$this->undanganId}/delete");

        $this->dontSeeInDatabase('undangan_rapat', ['id' => $this->undanganId]);
        $this->assertSame(0, $this->db->table('daftar_hadir')->countAllResults());
        $this->assertSame(0, $this->db->table('berita_acara')->countAllResults());
        $this->assertSame(0, $this->db->table('dokumen_rapat')->countAllResults());
        $this->assertFileDoesNotExist($dok['path']);
    }

    public function testBeritaAcaraSatuPerUndangan(): void
    {
        $this->db->table('berita_acara')->insert(['undangan_id' => $this->undanganId, 'uraian' => 'A', 'created_by' => $this->userId]);

        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $this->db->table('berita_acara')->insert(['undangan_id' => $this->undanganId, 'uraian' => 'B', 'created_by' => $this->userId]);
    }
}
