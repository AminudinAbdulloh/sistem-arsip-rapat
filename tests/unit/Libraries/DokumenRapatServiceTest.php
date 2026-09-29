<?php

use App\Libraries\DokumenRapatService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\FakeUploadedFile;

/**
 * Validasi dan pencatatan unggahan dokumen rapat.
 *
 * Unit test CLI tidak melalui unggahan HTTP sungguhan, sehingga berkas dibungkus
 * FakeUploadedFile (move() tidak memindahkan berkas fisik).
 *
 * @internal
 */
final class DokumenRapatServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    private string $tmpDir;
    private int $userId;
    private int $undanganId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dokumen_unit_' . uniqid();
        mkdir($this->tmpDir, 0777, true);

        $this->db->table('users')->insert(['nip' => '1', 'nama' => 'S', 'kata_sandi' => 'x', 'role' => 'sekretaris']);
        $this->userId = (int) $this->db->insertID();
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $this->userId,
        ]);
        $this->undanganId = (int) $this->db->insertID();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . DIRECTORY_SEPARATOR . '{,out' . DIRECTORY_SEPARATOR . '}*', GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        @rmdir($this->tmpDir . DIRECTORY_SEPARATOR . 'out');
        @rmdir($this->tmpDir);
        parent::tearDown();
    }

    private function fake(string $name, string $content = 'isi', ?int $size = null): FakeUploadedFile
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'src_' . $name;
        file_put_contents($path, $content);

        return new FakeUploadedFile($path, $name, 'application/octet-stream', $size);
    }

    private function service(): DokumenRapatService
    {
        return new DokumenRapatService($this->tmpDir . DIRECTORY_SEPARATOR . 'out' . DIRECTORY_SEPARATOR);
    }

    public function testTanpaBerkasDitolak(): void
    {
        $error = $this->service()->simpan($this->undanganId, 'Judul', null, $this->userId);

        $this->assertSame('Berkas wajib dipilih dan harus berhasil diunggah.', $error);
        $this->assertSame(0, $this->db->table('dokumen_rapat')->countAllResults());
    }

    public function testBerkasGagalUnggahDitolak(): void
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'x.pdf';
        file_put_contents($path, 'x');
        $file = new FakeUploadedFile($path, 'x.pdf', 'application/pdf', 1, UPLOAD_ERR_PARTIAL);

        $error = $this->service()->simpan($this->undanganId, 'Judul', $file, $this->userId);

        $this->assertSame('Berkas wajib dipilih dan harus berhasil diunggah.', $error);
    }

    /**
     * @dataProvider ekstensiTerlarangProvider
     */
    public function testEkstensiTidakDiizinkanDitolak(string $nama): void
    {
        $error = $this->service()->simpan($this->undanganId, 'Judul', $this->fake($nama), $this->userId);

        $this->assertStringStartsWith('Tipe berkas tidak diizinkan.', (string) $error);
        $this->assertSame(0, $this->db->table('dokumen_rapat')->countAllResults());
    }

    public static function ekstensiTerlarangProvider(): array
    {
        return [
            'php'          => ['shell.php'],
            'exe'          => ['virus.exe'],
            'html'         => ['halaman.html'],
            'ganda'        => ['gambar.php.txt'],
            'tanpa ekstensi' => ['README'],
        ];
    }

    public function testUkuranMelebihiBatasDitolak(): void
    {
        $file = $this->fake('besar.pdf', 'x', DokumenRapatService::MAX_BYTES + 1);

        $error = $this->service()->simpan($this->undanganId, 'Judul', $file, $this->userId);

        $this->assertSame('Ukuran berkas maksimal 10 MB.', $error);
    }

    public function testJudulTerlaluPanjangDitolak(): void
    {
        $error = $this->service()->simpan($this->undanganId, str_repeat('a', 151), $this->fake('a.pdf'), $this->userId);

        $this->assertSame('Judul maksimal 150 karakter.', $error);
    }

    public function testBerkasValidDicatatDenganNamaAcak(): void
    {
        $error = $this->service()->simpan($this->undanganId, '  Materi Rapat  ', $this->fake('Materi Asli.PDF'), $this->userId);

        $this->assertNull($error);
        $row = $this->db->table('dokumen_rapat')->get()->getRowArray();
        $this->assertSame('Materi Rapat', $row['judul']);
        $this->assertSame('Materi Asli.PDF', $row['nama_asli']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{24}\.pdf$/', $row['berkas']);
        $this->assertSame((string) $this->undanganId, (string) $row['undangan_id']);
        $this->assertSame((string) $this->userId, (string) $row['uploaded_by']);
    }

    public function testJudulKosongMemakaiNamaBerkasTanpaEkstensi(): void
    {
        $this->service()->simpan($this->undanganId, '   ', $this->fake('Notulen Awal.docx'), $this->userId);

        $this->seeInDatabase('dokumen_rapat', ['judul' => 'Notulen Awal']);
    }

    public function testHapusBerkasFisik(): void
    {
        $dir  = $this->tmpDir . DIRECTORY_SEPARATOR . 'out' . DIRECTORY_SEPARATOR;
        mkdir($dir, 0777, true);
        file_put_contents($dir . 'abc.pdf', 'x');

        $this->service()->hapusBerkas('abc.pdf');
        $this->assertFileDoesNotExist($dir . 'abc.pdf');

        // tidak error bila berkas sudah tidak ada, dan tidak menyentuh path di luar folder
        $this->service()->hapusBerkas('abc.pdf');
        $this->service()->hapusBerkas('../../../etc/passwd');
        $this->assertTrue(true);
    }
}
