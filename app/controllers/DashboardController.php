<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/Undangan.php';
require_once BASE_PATH . '/app/models/Notulensi.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class DashboardController extends Controller
{
    private Undangan  $undanganModel;
    private Notulensi $notulensiModel;

    public function __construct()
    {
        $this->undanganModel  = new Undangan();
        $this->notulensiModel = new Notulensi();
    }

    // ----------------------------------------------------------------
    // Dashboard utama
    // ----------------------------------------------------------------

    public function index(): void
    {
        $this->requireLogin();

        $year  = (int) ($_GET['year']  ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('m'));

        $this->renderMain('Dashboard', 'dashboard/index', [
            'monthlyStats'   => $this->undanganModel->getMonthlyStats($year),
            'availableYears' => $this->undanganModel->getAvailableYears(),
            'totalUndangan'  => $this->undanganModel->count(),
            'totalNotulensi' => $this->notulensiModel->count(),
            'selectedYear'   => $year,
            'selectedMonth'  => $month,
        ]);
    }

    // ----------------------------------------------------------------
    // Download laporan
    // ----------------------------------------------------------------

    public function downloadBulanan(): void
    {
        $this->requireLogin();

        $year  = (int) ($_GET['year']  ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('m'));

        $namaBulan = DateHelper::listBulan();

        $this->renderLaporanHtml(
            judul:     "Laporan Bulanan — {$namaBulan[$month]} {$year}",
            undangan:  $this->undanganModel->getByMonth($year, $month),
            notulensi: $this->notulensiModel->getByMonth($year, $month),
        );
    }

    public function downloadTahunan(): void
    {
        $this->requireLogin();

        $year = (int) ($_GET['year'] ?? date('Y'));

        $this->renderLaporanHtml(
            judul:     "Laporan Tahunan — {$year}",
            undangan:  $this->undanganModel->getByYear($year),
            notulensi: $this->notulensiModel->getByYear($year),
        );
    }

    // ----------------------------------------------------------------
    // Private — render HTML laporan (printable)
    // ----------------------------------------------------------------

    /**
     * Render halaman HTML laporan dan trigger print di browser.
     * Template dipisahkan ke view agar controller tetap ramping.
     */
    private function renderLaporanHtml(string $judul, array $undangan, array $notulensi): void
    {
        date_default_timezone_set('Asia/Jakarta');
        header('Content-Type: text/html; charset=utf-8');

        $this->view('laporan/cetak', [
            'judul'     => $judul,
            'undangan'  => $undangan,
            'notulensi' => $notulensi,
            'namaBulan' => DateHelper::listBulan(),
            'cetakWaktu' => date('d/m/Y H:i') . ' WIB',
        ]);
        exit;
    }
}