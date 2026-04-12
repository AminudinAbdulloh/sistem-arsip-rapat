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

        $monthlyStats = $this->undanganModel->getMonthlyStats($year);
        $availableYears = $this->undanganModel->getAvailableYears();
        $totalUndangan = $this->undanganModel->count();
        $totalNotulensi = $this->notulensiModel->count();

        $this->view('layouts/main', [
            'title' => 'Dashboard',
            'content' => 'dashboard/index',
            'monthlyStats' => $monthlyStats,
            'availableYears' => $availableYears,
            'totalUndangan' => $totalUndangan,
            'totalNotulensi' => $totalNotulensi,
            'selectedYear' => $year,
            'selectedMonth' => $month,
        ]);
    }

    public function downloadBulanan($param = null) {
        $this->requireLogin();
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');

        $undangan = $this->undanganModel->getByMonth($year, $month);
        $notulensi = $this->notulensiModel->getByMonth($year, $month);
        $namaBulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        $this->generatePdfLaporan(
            "Laporan Bulanan - {$namaBulan[(int)$month]} {$year}",
            $undangan, $notulensi, "laporan_bulanan_{$year}_{$month}.pdf"
        );
    }

    public function downloadTahunan($param = null) {
        $this->requireLogin();
        $year = $_GET['year'] ?? date('Y');

        $undangan = $this->undanganModel->getByYear($year);
        $notulensi = $this->notulensiModel->getByYear($year);

        $this->generatePdfLaporan(
            "Laporan Tahunan - {$year}",
            $undangan, $notulensi, "laporan_tahunan_{$year}.pdf"
        );
    }

    private function generatePdfLaporan($judul, $undangan, $notulensi, $filename) {
        // Simple HTML to PDF using built-in output
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($judul) ?></title>
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
  h1 { color: #1a3a5c; text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 10px; }
  h2 { color: #2563eb; margin-top: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #1a3a5c; color: white; padding: 8px; text-align: left; }
  td { padding: 7px; border-bottom: 1px solid #ddd; }
  tr:nth-child(even) { background: #f0f4ff; }
  .header-logo { text-align: center; margin-bottom: 10px; }
  .subtitle { text-align: center; color: #555; margin-bottom: 20px; }
  @media print { body { margin: 0; } }
</style>
</head>
<body>
<div class="header-logo">
  <h1><?= htmlspecialchars($judul) ?></h1>
  <p class="subtitle">Institut Teknologi Dirgantara Adisutjipto - Sistem Informasi Pengelolaan Arsip Rapat</p>
  <p class="subtitle">Dicetak: <?= date('d/m/Y H:i') ?></p>
</div>

<h2>Data Undangan Rapat (<?= count($undangan) ?> data)</h2>
<?php if (empty($undangan)): ?>
  <p><em>Tidak ada data undangan rapat.</em></p>
<?php else: ?>
<table>
  <tr><th>No</th><th>Hari</th><th>Waktu</th><th>Tempat</th><th>Acara</th><th>Dibuat Oleh</th></tr>
  <?php foreach ($undangan as $i => $u): ?>
  <tr>
    <td><?= $i+1 ?></td>
    <td><?= htmlspecialchars($u['hari']) ?></td>
    <td><?= date('d/m/Y H:i', strtotime($u['waktu'])) ?></td>
    <td><?= htmlspecialchars($u['tempat']) ?></td>
    <td><?= htmlspecialchars($u['acara']) ?></td>
    <td><?= htmlspecialchars($u['pembuat']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Data Notulensi Rapat (<?= count($notulensi) ?> data)</h2>
<?php if (empty($notulensi)): ?>
  <p><em>Tidak ada data notulensi rapat.</em></p>
<?php else: ?>
<table>
  <tr><th>No</th><th>Tgl Rapat</th><th>Tema</th><th>Deskripsi</th><th>Catatan</th><th>Dibuat Oleh</th></tr>
  <?php foreach ($notulensi as $i => $n): ?>
  <tr>
    <td><?= $i+1 ?></td>
    <td><?= date('d/m/Y', strtotime($n['tgl_rapat'])) ?></td>
    <td><?= htmlspecialchars($n['tema_rapat']) ?></td>
    <td><?= nl2br(htmlspecialchars(substr($n['deskripsi_rapat'], 0, 100))) ?>...</td>
    <td><?= htmlspecialchars(substr($n['catatan'] ?? '', 0, 80)) ?></td>
    <td><?= htmlspecialchars($n['pembuat']) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>
<?php
        exit;
    }
}