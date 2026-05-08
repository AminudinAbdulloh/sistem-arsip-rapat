<?php

namespace App\Controllers;

use App\Models\UndanganRapatModel;

class UndanganController extends BaseController
{
    protected UndanganRapatModel $model;

    public function __construct()
    {
        $this->model = new UndanganRapatModel();
    }

    public function index(): string
    {
        $undangan = $this->model->findAllWithUser();
        return view('Undangan/index', [
            'title' => 'Undangan Rapat - Arsip ITD',
            'undangan' => $undangan,
        ]);
    }

    public function create(): string
    {
        return view('Undangan/create', [
            'title' => 'Tambah Undangan Rapat',
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $hari = $this->request->getPost('hari') ?? '';
        $waktu = $this->request->getPost('waktu') ?? '';
        $tempat = trim($this->request->getPost('tempat') ?? '');
        $acara = trim($this->request->getPost('acara') ?? '');

        if (empty($hari) || empty($waktu) || empty($tempat) || empty($acara)) {
            return redirect()->to('/undangan/create')->with('error', 'Semua field wajib diisi.');
        }

        $this->model->insert([
            'hari' => $hari,
            'waktu' => $waktu,
            'tempat' => $tempat,
            'acara' => $acara,
            'created_by' => session()->get('user')['id']
        ]);

        return redirect()->to('/undangan')->with('success', 'Undangan rapat berhasil ditambahkan.');
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $undangan = $this->model->findByIdWithUser($id);
        if (!$undangan) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak ditemukan.');
        }

        return view('Undangan/edit', [
            'title' => 'Edit Undangan Rapat',
            'undangan' => $undangan,
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $hari = $this->request->getPost('hari') ?? '';
        $waktu = $this->request->getPost('waktu') ?? '';
        $tempat = trim($this->request->getPost('tempat') ?? '');
        $acara = trim($this->request->getPost('acara') ?? '');

        if (empty($hari) || empty($waktu) || empty($tempat) || empty($acara)) {
            return redirect()->to('/undangan/' . $id . '/edit')->with('error', 'Semua field wajib diisi.');
        }

        $this->model->update($id, [
            'hari' => $hari,
            'waktu' => $waktu,
            'tempat' => $tempat,
            'acara' => $acara,
        ]);

        return redirect()->to('/undangan')->with('success', 'Undangan rapat berhasil diperbarui.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($this->model->hasNotulensi($id)) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak dapat dihapus karena sudah memiliki notulensi.');
        }
        $this->model->delete($id);
        return redirect()->to('/undangan')->with('success', 'Undangan rapat berhasil dihapus.');
    }

    public function downloadPdf(int $id): \CodeIgniter\HTTP\ResponseInterface|\CodeIgniter\HTTP\RedirectResponse
    {
        $undangan = $this->model->findByIdWithUser($id);
        if (!$undangan) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak ditemukan.');
        }

        $templatePath = FCPATH . 'template_undangan_rapat.docx';
        if (!file_exists($templatePath)) {
            return redirect()->to('/undangan')->with('error', 'Template undangan tidak ditemukan.');
        }

        try {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            // Format data untuk template
            $tanggalObj = new \DateTime($undangan['waktu']);
            $hari = $undangan['hari'];
            $tanggal = $tanggalObj->format('j F Y');
            $waktuStr = $tanggalObj->format('H:i') . ' WIB - Selesai';

            // Isi placeholder di template
            $templateProcessor->setValue('PERIHAL', $undangan['acara']);
            $templateProcessor->setValue('HARI_TANGGAL', $hari . ' / ' . $tanggal);
            $templateProcessor->setValue('WAKTU', $waktuStr);
            $templateProcessor->setValue('TEMPAT', $undangan['tempat']);
            $templateProcessor->setValue('TANGGAL_PEMBUATAN', 'Yogyakarta, ' . date('j F Y'));

            // Generate file temporary
            $filename = 'Undangan_' . preg_replace('/[^a-zA-Z0-9]/', '_', $undangan['acara']) . '_' . $id . '.docx';
            $tempFile = WRITEPATH . 'uploads/' . $filename;

            // Pastikan folder ada
            if (!is_dir(WRITEPATH . 'uploads')) {
                mkdir(WRITEPATH . 'uploads', 0777, true);
            }

            $templateProcessor->saveAs($tempFile);

            // Return file download
            return $this->response
                ->setContentType('application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody(file_get_contents($tempFile));

        } catch (\Exception $e) {
            return redirect()->to('/undangan')->with('error', 'Gagal generate undangan: ' . $e->getMessage());
        }
    }

    private function generateUndanganHTML(array $u, string $waktu): string
    {
        $namaUser = session()->get('user')['nama'] ?? 'Admin';
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
