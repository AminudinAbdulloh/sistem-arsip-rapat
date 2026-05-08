<?php

namespace App\Controllers;

use App\Models\NotulensiRapatModel;
use App\Models\UndanganRapatModel;

class NotulensiController extends BaseController
{
    protected NotulensiRapatModel $model;
    protected UndanganRapatModel $undanganModel;

    public function __construct()
    {
        $this->model = new NotulensiRapatModel();
        $this->undanganModel = new UndanganRapatModel();
    }

    public function index(): string
    {
        $notulensi = $this->model->findAllWithRelations();
        return view('Notulensi/index', [
            'title' => 'Notulensi Rapat - Arsip ITD',
            'notulensi' => $notulensi,
        ]);
    }

    public function create(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        // Ambil semua undangan yang belum memiliki notulensi
        $existingNotulensi = $this->model->findAll();
        $excludeUndanganIds = array_column($existingNotulensi, 'undangan_id');

        if (!empty($excludeUndanganIds)) {
            $undangan = $this->undanganModel
                ->whereNotIn('id', $excludeUndanganIds)
                ->findAll();
        } else {
            $undangan = $this->undanganModel->findAll();
        }

        if (empty($undangan)) {
            return redirect()->to('/notulensi')->with('error', 'Tidak ada undangan rapat yang tersedia. Semua undangan sudah memiliki notulensi.');
        }

        return view('Notulensi/create', [
            'title' => 'Tambah Notulensi Rapat',
            'undangan' => $undangan,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $undanganId = (int)($this->request->getPost('undangan_id') ?? 0);
        $tglRapat = $this->request->getPost('tgl_rapat') ?? '';
        $temaRapat = trim($this->request->getPost('tema_rapat') ?? '');
        $deskripsi = trim($this->request->getPost('deskripsi_rapat') ?? '');
        $catatan = trim($this->request->getPost('catatan') ?? '');

        if (!$undanganId || empty($tglRapat) || empty($temaRapat) || empty($deskripsi)) {
            return redirect()->to('/notulensi/create')->with('error', 'Field yang wajib diisi belum lengkap.');
        }

        // Validasi undangan ada
        $undangan = $this->undanganModel->find($undanganId);
        if (!$undangan) {
            return redirect()->to('/notulensi/create')->with('error', 'Undangan tidak ditemukan.');
        }

        // Validasi: satu undangan hanya boleh memiliki satu notulensi
        $existingNotulensi = $this->model->where('undangan_id', $undanganId)->first();
        if ($existingNotulensi) {
            return redirect()->to('/notulensi/create')->with('error', 'Undangan rapat ini sudah memiliki notulensi. Silakan pilih undangan lain.');
        }

        // Handle upload dokumentasi
        $dokumentasi = null;
        $file = $this->request->getFile('dokumentasi');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dokumentasi = $this->uploadFoto($file);
            if (!$dokumentasi) {
                return redirect()->to('/notulensi/create')->with('error', 'Gagal mengunggah foto dokumentasi. Format harus JPG, PNG, GIF, atau WEBP.');
            }
        }

        $this->model->insert([
            'undangan_id' => $undanganId,
            'tgl_rapat' => $tglRapat,
            'tema_rapat' => $temaRapat,
            'deskripsi_rapat' => $deskripsi,
            'catatan' => $catatan,
            'dokumentasi' => $dokumentasi,
            'created_by' => session()->get('user')['id']
        ]);

        return redirect()->to('/notulensi')->with('success', 'Notulensi rapat berhasil ditambahkan.');
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $notulensi = $this->model->findByIdWithRelations($id);
        if (!$notulensi) {
            return redirect()->to('/notulensi')->with('error', 'Notulensi tidak ditemukan.');
        }

        $undangan = $this->undanganModel->findAll();
        return view('Notulensi/edit', [
            'title' => 'Edit Notulensi Rapat',
            'notulensi' => $notulensi,
            'undangan' => $undangan,
        ]);
    }

    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $notulensi = $this->model->find($id);
        if (!$notulensi) {
            return redirect()->to('/notulensi')->with('error', 'Notulensi tidak ditemukan.');
        }

        $undanganId = (int)($this->request->getPost('undangan_id') ?? 0);
        $tglRapat = $this->request->getPost('tgl_rapat') ?? '';
        $temaRapat = trim($this->request->getPost('tema_rapat') ?? '');
        $deskripsi = trim($this->request->getPost('deskripsi_rapat') ?? '');
        $catatan = trim($this->request->getPost('catatan') ?? '');

        if (!$undanganId || empty($tglRapat) || empty($temaRapat) || empty($deskripsi)) {
            return redirect()->to('/notulensi/' . $id . '/edit')->with('error', 'Field yang wajib diisi belum lengkap.');
        }

        // Handle upload dokumentasi baru
        $dokumentasi = $notulensi['dokumentasi'];
        $file = $this->request->getFile('dokumentasi');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newDok = $this->uploadFoto($file);
            if ($newDok) {
                // Hapus foto lama
                if ($dokumentasi && file_exists(FCPATH . 'uploads/dokumentasi/' . $dokumentasi)) {
                    unlink(FCPATH . 'uploads/dokumentasi/' . $dokumentasi);
                }
                $dokumentasi = $newDok;
            }
        }

        $this->model->update($id, [
            'undangan_id' => $undanganId,
            'tgl_rapat' => $tglRapat,
            'tema_rapat' => $temaRapat,
            'deskripsi_rapat' => $deskripsi,
            'catatan' => $catatan,
            'dokumentasi' => $dokumentasi,
        ]);

        return redirect()->to('/notulensi')->with('success', 'Notulensi rapat berhasil diperbarui.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $notulensi = $this->model->find($id);
        if ($notulensi && $notulensi['dokumentasi']) {
            $fotoPath = FCPATH . 'uploads/dokumentasi/' . $notulensi['dokumentasi'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        $this->model->delete($id);
        return redirect()->to('/notulensi')->with('success', 'Notulensi rapat berhasil dihapus.');
    }

    public function show(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $notulensi = $this->model->findByIdWithRelations($id);
        if (!$notulensi) {
            return redirect()->to('/notulensi')->with('error', 'Notulensi tidak ditemukan.');
        }

        return view('Notulensi/show', [
            'title' => 'Detail Notulensi Rapat',
            'notulensi' => $notulensi,
        ]);
    }

    private function uploadFoto($file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return null;
        }

        $ext = $file->getExtension();
        $filename = uniqid('dok_') . '.' . $ext;
        $uploadPath = FCPATH . 'uploads/dokumentasi/';

        if ($file->move($uploadPath, $filename)) {
            return $filename;
        }

        return null;
    }
}
