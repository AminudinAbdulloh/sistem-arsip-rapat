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

            // Format tanggal Indonesia
            $bulanId = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $tglNum    = (int) $tanggalObj->format('j');
            $bulanNum  = (int) $tanggalObj->format('n');
            $tahun     = $tanggalObj->format('Y');
            $tanggalStr = $tglNum . ' ' . $bulanId[$bulanNum] . ' ' . $tahun;

            // Tanggal pembuatan (hari ini)
            $todayNum   = (int) date('j');
            $todayBulan = (int) date('n');
            $todayTahun = date('Y');
            $tanggalBuat = $todayNum . ' ' . $bulanId[$todayBulan] . ' ' . $todayTahun;

            // Waktu rapat
            $waktuStr = $tanggalObj->format('H.i') . ' WIB - Selesai';

            // Isi placeholder di template
            $templateProcessor->setValue('PERIHAL', $undangan['acara']);
            $templateProcessor->setValue('HARI_TANGGAL', $undangan['hari'] . ' / ' . $tanggalStr);
            $templateProcessor->setValue('WAKTU', $waktuStr);
            $templateProcessor->setValue('TEMPAT', $undangan['tempat']);
            $templateProcessor->setValue('TANGGAL_PEMBUATAN', $tanggalBuat);

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
}