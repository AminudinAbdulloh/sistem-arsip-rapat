<?php

use App\Controllers\NotulensiController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ReflectionHelper;
use Tests\Support\FakeUploadedFile;

/**
 * Pengujian White-Box (unit murni) terhadap NotulensiController::uploadFoto().
 *
 * uploadFoto() adalah method private sehingga diakses langsung melalui
 * ReflectionHelper (teknik unit testing standar CodeIgniter 4), tanpa
 * melibatkan HTTP request maupun database. Dua jalur percabangan diuji:
 *  1. Tipe MIME berkas tidak termasuk daftar yang diizinkan -> return null
 *     (berkas ditolak sebelum proses pemindahan berkas dilakukan).
 *  2. Tipe MIME berkas termasuk gambar yang diizinkan -> berkas "dipindahkan"
 *     dan nama berkas unik dikembalikan.
 *
 * Karena unit test CLI tidak melalui transaksi upload HTTP sungguhan,
 * is_uploaded_file()/move_uploaded_file() bawaan PHP selalu false untuk
 * berkas apa pun. Jalur ke-2 karena itu diuji menggunakan FakeUploadedFile,
 * sebuah test double yang meng-override move() agar hasil "berhasil disimpan"
 * dapat diverifikasi tanpa operasi berkas fisik nyata.
 *
 * @internal
 */
final class NotulensiUploadFotoTest extends CIUnitTestCase
{
    use ReflectionHelper;

    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'notulensi_unit_' . uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach (glob($this->tmpDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            unlink($file);
        }
        @rmdir($this->tmpDir);
    }

    /**
     * @return mixed
     */
    private function invokeUploadFoto(NotulensiController $controller, $file)
    {
        $invoker = self::getPrivateMethodInvoker($controller, 'uploadFoto');

        return $invoker($file);
    }

    public function testJalur1BerkasBertipeMimeTidakValidDitolak(): void
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'dokumen.txt';
        file_put_contents($path, 'Ini adalah berkas teks biasa, bukan gambar.');

        $file = new FakeUploadedFile($path, 'dokumen.txt', 'text/plain');

        $result = $this->invokeUploadFoto(new NotulensiController(), $file);

        $this->assertNull(
            $result,
            'Berkas dengan tipe MIME di luar daftar image/jpeg, image/png, image/gif, image/webp harus ditolak (null).',
        );
    }

    public function testJalur2BerkasBertipeGambarValidDiterima(): void
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'foto.jpg';
        imagejpeg(imagecreatetruecolor(2, 2), $path);

        $file = new FakeUploadedFile($path, 'foto.jpg', 'image/jpeg');

        $result = $this->invokeUploadFoto(new NotulensiController(), $file);

        $this->assertIsString($result, 'Berkas gambar yang valid harus mengembalikan nama berkas hasil unggahan.');
        $this->assertStringStartsWith('dok_', $result);
        $this->assertStringEndsWith('.jpg', $result);
    }

    public function testJalur2BerkasGifValidJugaDiterima(): void
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'foto.gif';
        imagegif(imagecreatetruecolor(2, 2), $path);

        $file = new FakeUploadedFile($path, 'foto.gif', 'image/gif');

        $result = $this->invokeUploadFoto(new NotulensiController(), $file);

        $this->assertIsString($result);
        $this->assertStringEndsWith('.gif', $result);
    }
}
