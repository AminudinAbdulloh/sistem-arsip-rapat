<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/Undangan.php';

class UndanganController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new Undangan();
    }

    public function index($param = null) {
        $this->requireLogin();
        $undangan = $this->model->getAll();
        $this->view('layouts/main', [
            'title' => 'Undangan Rapat',
            'content' => 'undangan/index',
            'undangan' => $undangan,
        ]);
    }

    public function create($param = null) {
        $this->requireLogin();
        $error = '';
        $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'hari' => $_POST['hari'] ?? '',
                'waktu' => $_POST['waktu'] ?? '',
                'tempat' => trim($_POST['tempat'] ?? ''),
                'acara' => trim($_POST['acara'] ?? ''),
                'dibuat_oleh' => $_SESSION['user_id'],
            ];
            if (empty($data['hari']) || empty($data['waktu']) || empty($data['tempat']) || empty($data['acara'])) {
                $error = 'Semua field harus diisi.';
            } else {
                if ($this->model->create($data)) {
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Undangan rapat berhasil ditambahkan.'];
                    $this->redirect('undangan');
                } else {
                    $error = 'Gagal menyimpan data.';
                }
            }
        }

        $this->view('layouts/main', [
            'title' => 'Tambah Undangan Rapat',
            'content' => 'undangan/form',
            'error' => $error,
            'hariList' => $hariList,
            'undangan' => null,
        ]);
    }

    public function edit($id) {
        $this->requireLogin();
        $undangan = $this->model->getById($id);
        if (!$undangan) { $this->redirect('undangan'); }

        $error = '';
        $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'hari' => $_POST['hari'] ?? '',
                'waktu' => $_POST['waktu'] ?? '',
                'tempat' => trim($_POST['tempat'] ?? ''),
                'acara' => trim($_POST['acara'] ?? ''),
            ];
            if (empty($data['hari']) || empty($data['waktu']) || empty($data['tempat']) || empty($data['acara'])) {
                $error = 'Semua field harus diisi.';
            } else {
                if ($this->model->update($id, $data)) {
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Undangan rapat berhasil diperbarui.'];
                    $this->redirect('undangan');
                } else {
                    $error = 'Gagal memperbarui data.';
                }
            }
        }

        $this->view('layouts/main', [
            'title' => 'Edit Undangan Rapat',
            'content' => 'undangan/form',
            'error' => $error,
            'hariList' => $hariList,
            'undangan' => $undangan,
        ]);
    }

    public function delete($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Undangan rapat berhasil dihapus.'];
        }
        $this->redirect('undangan');
    }

    public function pdf($id) {
        $this->requireLogin();
        $u = $this->model->getById($id);
        if (!$u) { $this->redirect('undangan'); }

        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Undangan Rapat</title>
<style>
  body { font-family: Arial, sans-serif; margin: 40px; color: #222; }
  .kop { text-align: center; border-bottom: 3px double #1a3a5c; padding-bottom: 15px; margin-bottom: 20px; }
  .kop h2 { margin: 0; color: #1a3a5c; font-size: 16px; }
  .kop h3 { margin: 4px 0 0; color: #1a3a5c; font-size: 13px; font-weight: normal; }
  .judul { text-align: center; margin: 20px 0; text-decoration: underline; font-weight: bold; font-size: 14px; }
  table.detail { margin: 20px 0; width: 100%; }
  table.detail td { padding: 6px 4px; vertical-align: top; }
  table.detail td:first-child { width: 130px; font-weight: bold; }
  table.detail td:nth-child(2) { width: 10px; }
  .ttd { margin-top: 50px; display: flex; justify-content: flex-end; }
  .ttd-box { text-align: center; min-width: 200px; }
  .ttd-box .ttd-space { height: 70px; }
  @media print { body { margin: 20px; } }
</style>
</head>
<body>
<div class="kop">
  <h2>INSTITUT TEKNOLOGI DIRGANTARA ADISUTJIPTO</h2>
  <h3>Program Studi - Sistem Informasi Pengelolaan Arsip Rapat</h3>
</div>
<div class="judul">UNDANGAN RAPAT</div>
<table class="detail">
  <tr><td>Hari</td><td>:</td><td><?= htmlspecialchars($u['hari']) ?></td></tr>
  <tr><td>Waktu</td><td>:</td><td><?= date('d/m/Y H:i', strtotime($u['waktu'])) ?> WIB</td></tr>
  <tr><td>Tempat</td><td>:</td><td><?= htmlspecialchars($u['tempat']) ?></td></tr>
  <tr><td>Acara</td><td>:</td><td><?= nl2br(htmlspecialchars($u['acara'])) ?></td></tr>
</table>
<p>Demikian undangan ini kami sampaikan. Atas perhatian dan kehadiran Bapak/Ibu, kami mengucapkan terima kasih.</p>
<div class="ttd">
  <div class="ttd-box">
    <p>Yogyakarta, <?= date('d/m/Y') ?></p>
    <p>Hormat Kami,</p>
    <div class="ttd-space"></div>
    <p><strong><?= htmlspecialchars($u['pembuat']) ?></strong></p>
  </div>
</div>
<script>window.onload = function() { window.print(); }</script>
</body>
</html>
<?php
        exit;
    }
}