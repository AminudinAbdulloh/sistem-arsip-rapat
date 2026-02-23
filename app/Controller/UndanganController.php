<?php

namespace ArsipRapat\Controller;

use ArsipRapat\App\View;
use ArsipRapat\Model\UndanganRapat;

class UndanganController
{
    private UndanganRapat $model;

    public function __construct()
    {
        $this->model = new UndanganRapat();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $undangan = $this->model->findAll();
        View::renderWithLayout('Undangan/index', [
            'title' => 'Undangan Rapat - Arsip ITD',
            'undangan' => $undangan,
        ]);
    }

    public function create(): void
    {
        View::renderWithLayout('Undangan/create', [
            'title' => 'Tambah Undangan Rapat',
        ]);
    }

    public function store(): void
    {
        $hari = $_POST['hari'] ?? '';
        $waktu = $_POST['waktu'] ?? '';
        $tempat = trim($_POST['tempat'] ?? '');
        $acara = trim($_POST['acara'] ?? '');

        if (empty($hari) || empty($waktu) || empty($tempat) || empty($acara)) {
            $_SESSION['error'] = 'Semua field wajib diisi.';
            header('Location: /undangan/create');
            exit();
        }

        $this->model->create([
            'hari' => $hari,
            'waktu' => $waktu,
            'tempat' => $tempat,
            'acara' => $acara,
            'created_by' => $_SESSION['user']['id']
        ]);

        $_SESSION['success'] = 'Undangan rapat berhasil ditambahkan.';
        header('Location: /undangan');
        exit();
    }

    public function edit(string $id): void
    {
        $undangan = $this->model->findById((int)$id);
        if (!$undangan) {
            $_SESSION['error'] = 'Undangan tidak ditemukan.';
            header('Location: /undangan');
            exit();
        }

        View::renderWithLayout('Undangan/edit', [
            'title' => 'Edit Undangan Rapat',
            'undangan' => $undangan,
        ]);
    }

    public function update(string $id): void
    {
        $hari = $_POST['hari'] ?? '';
        $waktu = $_POST['waktu'] ?? '';
        $tempat = trim($_POST['tempat'] ?? '');
        $acara = trim($_POST['acara'] ?? '');

        if (empty($hari) || empty($waktu) || empty($tempat) || empty($acara)) {
            $_SESSION['error'] = 'Semua field wajib diisi.';
            header('Location: /undangan/' . $id . '/edit');
            exit();
        }

        $this->model->update((int)$id, [
            'hari' => $hari,
            'waktu' => $waktu,
            'tempat' => $tempat,
            'acara' => $acara,
        ]);

        $_SESSION['success'] = 'Undangan rapat berhasil diperbarui.';
        header('Location: /undangan');
        exit();
    }

    public function delete(string $id): void
    {
        if ($this->model->hasNotulensi((int)$id)) {
            $_SESSION['error'] = 'Undangan tidak dapat dihapus karena sudah memiliki notulensi.';
            header('Location: /undangan');
            exit();
        }
        $this->model->delete((int)$id);
        $_SESSION['success'] = 'Undangan rapat berhasil dihapus.';
        header('Location: /undangan');
        exit();
    }

    public function downloadPdf(string $id): void
    {
        $undangan = $this->model->findById((int)$id);
        if (!$undangan) {
            $_SESSION['error'] = 'Undangan tidak ditemukan.';
            header('Location: /undangan');
            exit();
        }

        $waktu = date('d F Y, H:i', strtotime($undangan['waktu']));
        $html = $this->generateUndanganHTML($undangan, $waktu);

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="undangan_rapat_' . $id . '.html"');
        echo $html;
        exit();
    }

    private function generateUndanganHTML(array $u, string $waktu): string
    {
        $namaUser = $_SESSION['user']['nama'];
        $tanggalBuat = date('d F Y');

        return "<!DOCTYPE html>
<html lang='id'>
<head>
<meta charset='UTF-8'>
<title>Undangan Rapat</title>
<style>
  body { font-family: 'Times New Roman', serif; margin: 60px 80px; color: #000; }
  .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 30px; }
  .logo-area { display: flex; align-items: center; justify-content: center; gap: 20px; margin-bottom: 10px; }
  .org-name h1 { font-size: 16px; margin: 0; font-weight: bold; letter-spacing: 1px; }
  .org-name h2 { font-size: 14px; margin: 3px 0; font-weight: normal; }
  .org-name p { font-size: 12px; margin: 2px 0; }
  .doc-title { text-align: center; margin: 30px 0; }
  .doc-title h2 { font-size: 16px; text-decoration: underline; letter-spacing: 2px; }
  .doc-title p { font-size: 12px; margin: 5px 0; }
  .content { margin: 30px 0; line-height: 2; font-size: 13px; }
  .content table { width: 100%; }
  .content td:first-child { width: 150px; vertical-align: top; }
  .content td:nth-child(2) { width: 20px; }
  .content .value { font-weight: bold; }
  .body-text { font-size: 13px; line-height: 1.8; margin: 20px 0; text-align: justify; }
  .sign { margin-top: 50px; float: right; text-align: center; width: 200px; }
  .sign p { margin: 3px 0; font-size: 13px; }
  .sign .name { margin-top: 60px; font-weight: bold; text-decoration: underline; }
  .clear { clear: both; }
  @media print { body { margin: 20px 40px; } }
</style>
</head>
<body>
<div class='header'>
  <div class='org-name'>
    <h1>PROGRAM STUDI ITD ADISUTJIPTO</h1>
    <h2>LEMBAGA PENDIDIKAN DAN PELATIHAN TNI AU</h2>
    <p>Jl. Janti, Lanud Adisutjipto, Yogyakarta 55282</p>
  </div>
</div>

<div class='doc-title'>
  <h2>UNDANGAN RAPAT</h2>
  <p>Nomor: UR-" . str_pad($u['id'], 4, '0', STR_PAD_LEFT) . "/" . date('Y') . "</p>
</div>

<div class='body-text'>
  <p>Dengan hormat,</p>
  <p>Mengundang Bapak/Ibu untuk hadir dalam rapat yang akan diselenggarakan dengan keterangan sebagai berikut:</p>
</div>

<div class='content'>
  <table>
    <tr><td>Hari</td><td>:</td><td class='value'>{$u['hari']}</td></tr>
    <tr><td>Waktu</td><td>:</td><td class='value'>{$waktu} WIB</td></tr>
    <tr><td>Tempat</td><td>:</td><td class='value'>{$u['tempat']}</td></tr>
    <tr><td>Acara</td><td>:</td><td class='value'>{$u['acara']}</td></tr>
  </table>
</div>

<div class='body-text'>
  <p>Mengingat pentingnya rapat tersebut, dimohon kehadiran Bapak/Ibu tepat pada waktunya. Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.</p>
</div>

<div class='sign'>
  <p>Yogyakarta, {$tanggalBuat}</p>
  <p>Kepala Program Studi</p>
  <p class='name'>{$namaUser}</p>
</div>
<div class='clear'></div>

<script>window.onload = function() { window.print(); }</script>
</body></html>";
    }
}
