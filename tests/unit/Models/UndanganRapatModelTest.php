<?php

use App\Models\UndanganRapatModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Pengujian White-Box (unit) untuk App\Models\UndanganRapatModel.
 * Fokus pada method hasNotulensi() (dua jalur: ada/tidak ada notulensi terkait)
 * serta method rekapitulasi countByMonth(), countByYear(), dan getAvailableYears().
 *
 * @internal
 */
final class UndanganRapatModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->table('users')->insert([
            'nip'        => '198001012005011001',
            'nama'       => 'Administrator ITD',
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
            'jabatan'    => 'Kepala Program Studi',
        ]);
        $this->userId = (int) $this->db->insertID();
    }

    private function buatUndangan(string $waktu, string $acara = 'Rapat Uji'): int
    {
        $model = new UndanganRapatModel();

        return (int) $model->insert([
            'hari'       => 'Senin',
            'waktu'      => $waktu,
            'tempat'     => 'Ruang Uji',
            'acara'      => $acara,
            'created_by' => $this->userId,
        ]);
    }

    public function testHasNotulensiFalseSaatUndanganBelumMemilikiNotulensi(): void
    {
        $model      = new UndanganRapatModel();
        $undanganId = $this->buatUndangan('2026-10-05 09:00:00');

        $this->assertFalse($model->hasNotulensi($undanganId));
    }

    public function testHasNotulensiTrueSaatUndanganSudahMemilikiNotulensi(): void
    {
        $model      = new UndanganRapatModel();
        $undanganId = $this->buatUndangan('2026-10-05 09:00:00');

        $this->db->table('notulensi_rapat')->insert([
            'undangan_id'     => $undanganId,
            'deskripsi_rapat' => 'Hasil rapat uji',
            'created_by'      => $this->userId,
        ]);

        $this->assertTrue($model->hasNotulensi($undanganId));
    }

    public function testCountByMonthDanCountByYearMenghitungSesuaiPeriode(): void
    {
        $model = new UndanganRapatModel();

        $this->buatUndangan('2026-10-05 09:00:00', 'Rapat Oktober 1');
        $this->buatUndangan('2026-10-20 09:00:00', 'Rapat Oktober 2');
        $this->buatUndangan('2026-11-02 09:00:00', 'Rapat November');

        $this->assertSame(2, $model->countByMonth(10, 2026));
        $this->assertSame(1, $model->countByMonth(11, 2026));
        $this->assertSame(0, $model->countByMonth(1, 2025));
        $this->assertSame(3, $model->countByYear(2026));
    }

    public function testGetAvailableYearsMengembalikanTahunYangAda(): void
    {
        $model = new UndanganRapatModel();

        $this->buatUndangan('2025-01-05 09:00:00', 'Rapat 2025');
        $this->buatUndangan('2026-01-05 09:00:00', 'Rapat 2026');

        $years = array_map('intval', $model->getAvailableYears());

        $this->assertContains(2025, $years);
        $this->assertContains(2026, $years);
    }
}
