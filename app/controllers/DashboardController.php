<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/Undangan.php';
require_once BASE_PATH . '/app/models/Notulensi.php';

class DashboardController extends Controller {
    private $undanganModel;
    private $notulensiModel;

    public function __construct() {
        $this->undanganModel = new Undangan();
        $this->notulensiModel = new Notulensi();
    }

    public function index($param = null) {
        $this->requireLogin();
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        $monthlyStats   = $this->undanganModel->getMonthlyStats($year);
        $availableYears = $this->undanganModel->getAvailableYears();
        $totalUndangan  = $this->undanganModel->count();
        $totalNotulensi = $this->notulensiModel->count();

        $this->view('layouts/main', [
            'title'         => 'Dashboard',
            'content'       => 'dashboard/index',
            'monthlyStats'  => $monthlyStats,
            'availableYears'=> $availableYears,
            'totalUndangan' => $totalUndangan,
            'totalNotulensi'=> $totalNotulensi,
            'selectedYear'  => $year,
            'selectedMonth' => $month,
        ]);
    }

    public function downloadBulanan($param = null) {
        $this->requireLogin();
        $year  = $_GET['year']  ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        $undangan  = $this->undanganModel->getByMonth($year, $month);
        $notulensi = $this->notulensiModel->getByMonth($year, $month);
        $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];

        $this->generatePdfLaporan(
            "Laporan Bulanan - {$namaBulan[(int)$month]} {$year}",
            $undangan, $notulensi,
            "laporan_bulanan_{$year}_{$month}.pdf"
        );
    }

    public function downloadTahunan($param = null) {
        $this->requireLogin();
        $year = $_GET['year'] ?? date('Y');

        $undangan  = $this->undanganModel->getByYear($year);
        $notulensi = $this->notulensiModel->getByYear($year);

        $this->generatePdfLaporan(
            "Laporan Tahunan - {$year}",
            $undangan, $notulensi,
            "laporan_tahunan_{$year}.pdf"
        );
    }

    private function generatePdfLaporan($judul, $undangan, $notulensi, $filename) {
        // Set timezone sesuai WIB sebelum generate waktu cetak
        date_default_timezone_set('Asia/Jakarta');

        header('Content-Type: text/html; charset=utf-8');
        $namaBulanIndo = ['','Januari','Februari','Maret','April','Mei','Juni',
                          'Juli','Agustus','September','Oktober','November','Desember'];
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($judul) ?></title>
<style>
  /* Sembunyikan header/footer bawaan browser saat print */
  @page { margin: 1.5cm; }
  @media print {
    body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
  body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
  h1 { color: #1a3a5c; text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 10px; margin-bottom: 4px; }
  h2 { color: #2563eb; margin-top: 24px; margin-bottom: 8px; font-size: 14px; }
  .subtitle { text-align: center; color: #555; margin: 4px 0; font-size: 11px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #1a3a5c; color: white; padding: 8px 10px; text-align: left; font-size: 12px; }
  td { padding: 8px 10px; border-bottom: 1px solid #ddd; vertical-align: top; font-size: 12px; }
  tr:nth-child(even) td { background: #f0f4ff; }
  .deskripsi { white-space: pre-wrap; line-height: 1.5; }
  .catatan-cell { white-space: pre-wrap; line-height: 1.5; color: #374151; }
  .no-data { font-style: italic; color: #888; padding: 12px 0; }
</style>
</head>
<body>
<h1><?= htmlspecialchars($judul) ?></h1>
<p class="subtitle">Institut Teknologi Dirgantara Adisutjipto &mdash; Sistem Informasi Pengelolaan Arsip Rapat</p>
<p class="subtitle">Dicetak: <?= date('d/m/Y H:i') ?> WIB</p>

<h2>Data Undangan Rapat (<?= count($undangan) ?> data)</h2>
<?php if (empty($undangan)): ?>
  <p class="no-data">Tidak ada data undangan rapat.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th width="30">No</th>
      <th width="160">Hari / Tanggal</th>
      <th width="80">Waktu</th>
      <th width="160">Tempat</th>
      <th>Agenda / Perihal</th>
      <th width="110">Tgl Surat</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($undangan as $i => $u):
    $ts = strtotime($u['waktu']);
    $hariArr = ['','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
    $hariTgl = $hariArr[(int)date('N', $ts)] . ', '
             . (int)date('j', $ts) . ' '
             . $namaBulanIndo[(int)date('n', $ts)] . ' '
             . date('Y', $ts);
    $tglSurat = '';
    if (!empty($u['tgl_surat'])) {
        $ts2 = strtotime($u['tgl_surat']);
        $tglSurat = (int)date('j', $ts2) . ' '
                  . $namaBulanIndo[(int)date('n', $ts2)] . ' '
                  . date('Y', $ts2);
    }
  ?>
    <tr>
      <td><?= $i+1 ?></td>
      <td><?= htmlspecialchars($hariTgl) ?></td>
      <td><?= date('H:i', $ts) ?> WIB</td>
      <td><?= htmlspecialchars($u['tempat']) ?></td>
      <td class="deskripsi"><?= htmlspecialchars($u['acara']) ?></td>
      <td><?= htmlspecialchars($tglSurat) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<h2>Data Notulensi Rapat (<?= count($notulensi) ?> data)</h2>
<?php if (empty($notulensi)): ?>
  <p class="no-data">Tidak ada data notulensi rapat.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th width="30">No</th>
      <th width="140">Tanggal Rapat</th>
      <th width="180">Tema Rapat</th>
      <th>Deskripsi Rapat</th>
      <th width="180">Catatan</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($notulensi as $i => $n):
    $ts3 = strtotime($n['tgl_rapat']);
    $tglRapat = (int)date('j', $ts3) . ' '
              . $namaBulanIndo[(int)date('n', $ts3)] . ' '
              . date('Y', $ts3);
  ?>
    <tr>
      <td><?= $i+1 ?></td>
      <td><?= htmlspecialchars($tglRapat) ?></td>
      <td><?= htmlspecialchars($n['tema_rapat']) ?></td>
      <td class="deskripsi"><?= htmlspecialchars($n['deskripsi_rapat']) ?></td>
      <td class="catatan-cell"><?= htmlspecialchars($n['catatan'] ?? '-') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<script>
  // Hapus header/footer bawaan browser dengan margin @page sudah diset
  window.onload = function() { window.print(); };
</script>
</body>
</html>
        <?php
        exit;
    }
}