<?php

namespace App\Controllers;

use App\Models\BeritaAcaraModel;
use App\Models\DaftarHadirModel;
use App\Models\UndanganRapatModel;
use CodeIgniter\HTTP\RedirectResponse;

class BeritaAcaraController extends BaseController
{
    protected BeritaAcaraModel $model;

    public function __construct()
    {
        $this->model = new BeritaAcaraModel();
    }

    public function save(int $undanganId): RedirectResponse
    {
        if (! (new UndanganRapatModel())->find($undanganId)) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak ditemukan.');
        }

        $back = "/undangan/{$undanganId}/kelengkapan";
        $data = [
            'nomor'     => trim($this->request->getPost('nomor') ?? ''),
            'uraian'    => trim($this->request->getPost('uraian') ?? ''),
            'keputusan' => trim($this->request->getPost('keputusan') ?? ''),
        ];

        if (! $this->validateData($data, $this->model->rules())) {
            return redirect()->to($back)->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->model->simpan($undanganId, $data, (int) session()->get('user')['id']);

        return redirect()->to($back)->with('success', 'Berita acara berhasil disimpan.');
    }

    /** Halaman berita acara siap cetak (memakai window.print()). */
    public function cetak(int $undanganId): string|RedirectResponse
    {
        $undangan = (new UndanganRapatModel())->findByIdWithUser($undanganId);
        if (! $undangan) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak ditemukan.');
        }

        $beritaAcara = $this->model->findByUndangan($undanganId);
        if (! $beritaAcara) {
            return redirect()->to("/undangan/{$undanganId}/kelengkapan")->with('error', 'Berita acara belum dibuat.');
        }

        return view('Kelengkapan/cetak_berita_acara', [
            'undangan'    => $undangan,
            'beritaAcara' => $beritaAcara,
            'hadir'       => (new DaftarHadirModel())->findByUndangan($undanganId),
            'pencetak'    => session()->get('user')['nama'] ?? '',
        ]);
    }
}
