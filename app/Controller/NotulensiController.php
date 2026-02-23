<?php

namespace ArsipRapat\Controller;

use ArsipRapat\App\View;
use ArsipRapat\Model\NotulensiRapat;
use ArsipRapat\Model\UndanganRapat;

class NotulensiController
{
    private NotulensiRapat $model;
    private UndanganRapat $undanganModel;

    public function __construct()
    {
        $this->model = new NotulensiRapat();
        $this->undanganModel = new UndanganRapat();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $notulensi = $this->model->findAll();
        View::renderWithLayout('Notulensi/index', [
            'title' => 'Notulensi Rapat - Arsip ITD',
            'notulensi' => $notulensi,
        ]);
    }

    public function create(): void
    {
        $undangan = $this->undanganModel->findAll();
        if (empty($undangan)) {
            $_SESSION['error'] = 'Belum ada undangan rapat. Silakan buat undangan terlebih dahulu.';
            header('Location: /notulensi');
            exit();
        }

        View::renderWithLayout('Notulensi/create', [
            'title' => 'Tambah Notulensi Rapat',
            'undangan' => $undangan,
        ]);
    }

    public function store(): void
    {
        $undanganId = (int)($_POST['undangan_id'] ?? 0);
        $tglRapat = $_POST['tgl_rapat'] ?? '';
        $temaRapat = trim($_POST['tema_rapat'] ?? '');
        $deskripsi = trim($_POST['deskripsi_rapat'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');

        if (!$undanganId || empty($tglRapat) || empty($temaRapat) || empty($deskripsi)) {
            $_SESSION['error'] = 'Field yang wajib diisi belum lengkap.';
            header('Location: /notulensi/create');
            exit();
        }

        // Validasi undangan ada
        $undangan = $this->undanganModel->findById($undanganId);
        if (!$undangan) {
            $_SESSION['error'] = 'Undangan tidak ditemukan.';
            header('Location: /notulensi/create');
            exit();
        }

        // Handle upload dokumentasi
        $dokumentasi = null;
        if (isset($_FILES['dokumentasi']) && $_FILES['dokumentasi']['error'] === UPLOAD_ERR_OK) {
            $dokumentasi = $this->uploadFoto($_FILES['dokumentasi']);
            if (!$dokumentasi) {
                $_SESSION['error'] = 'Gagal mengunggah foto dokumentasi. Format harus JPG, PNG, atau GIF.';
                header('Location: /notulensi/create');
                exit();
            }
        }

        $this->model->create([
            'undangan_id' => $undanganId,
            'tgl_rapat' => $tglRapat,
            'tema_rapat' => $temaRapat,
            'deskripsi_rapat' => $deskripsi,
            'catatan' => $catatan,
            'dokumentasi' => $dokumentasi,
            'created_by' => $_SESSION['user']['id']
        ]);

        $_SESSION['success'] = 'Notulensi rapat berhasil ditambahkan.';
        header('Location: /notulensi');
        exit();
    }

    public function edit(string $id): void
    {
        $notulensi = $this->model->findById((int)$id);
        if (!$notulensi) {
            $_SESSION['error'] = 'Notulensi tidak ditemukan.';
            header('Location: /notulensi');
            exit();
        }

        $undangan = $this->undanganModel->findAll();
        View::renderWithLayout('Notulensi/edit', [
            'title' => 'Edit Notulensi Rapat',
            'notulensi' => $notulensi,
            'undangan' => $undangan,
        ]);
    }

    public function update(string $id): void
    {
        $notulensi = $this->model->findById((int)$id);
        if (!$notulensi) {
            $_SESSION['error'] = 'Notulensi tidak ditemukan.';
            header('Location: /notulensi');
            exit();
        }

        $undanganId = (int)($_POST['undangan_id'] ?? 0);
        $tglRapat = $_POST['tgl_rapat'] ?? '';
        $temaRapat = trim($_POST['tema_rapat'] ?? '');
        $deskripsi = trim($_POST['deskripsi_rapat'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');

        if (!$undanganId || empty($tglRapat) || empty($temaRapat) || empty($deskripsi)) {
            $_SESSION['error'] = 'Field yang wajib diisi belum lengkap.';
            header('Location: /notulensi/' . $id . '/edit');
            exit();
        }

        // Handle upload dokumentasi baru
        $dokumentasi = $notulensi['dokumentasi'];
        if (isset($_FILES['dokumentasi']) && $_FILES['dokumentasi']['error'] === UPLOAD_ERR_OK) {
            $newDok = $this->uploadFoto($_FILES['dokumentasi']);
            if ($newDok) {
                // Hapus foto lama
                if ($dokumentasi && file_exists(__DIR__ . '/../../uploads/dokumentasi/' . $dokumentasi)) {
                    unlink(__DIR__ . '/../../uploads/dokumentasi/' . $dokumentasi);
                }
                $dokumentasi = $newDok;
            }
        }

        $this->model->update((int)$id, [
            'undangan_id' => $undanganId,
            'tgl_rapat' => $tglRapat,
            'tema_rapat' => $temaRapat,
            'deskripsi_rapat' => $deskripsi,
            'catatan' => $catatan,
            'dokumentasi' => $dokumentasi,
        ]);

        $_SESSION['success'] = 'Notulensi rapat berhasil diperbarui.';
        header('Location: /notulensi');
        exit();
    }

    public function delete(string $id): void
    {
        $notulensi = $this->model->findById((int)$id);
        if ($notulensi && $notulensi['dokumentasi']) {
            $fotoPath = __DIR__ . '/../../uploads/dokumentasi/' . $notulensi['dokumentasi'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        $this->model->delete((int)$id);
        $_SESSION['success'] = 'Notulensi rapat berhasil dihapus.';
        header('Location: /notulensi');
        exit();
    }

    public function show(string $id): void
    {
        $notulensi = $this->model->findById((int)$id);
        if (!$notulensi) {
            $_SESSION['error'] = 'Notulensi tidak ditemukan.';
            header('Location: /notulensi');
            exit();
        }

        View::renderWithLayout('Notulensi/show', [
            'title' => 'Detail Notulensi Rapat',
            'notulensi' => $notulensi,
        ]);
    }

    private function uploadFoto(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('dok_') . '.' . $ext;
        $uploadPath = __DIR__ . '/../../uploads/dokumentasi/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $filename;
        }

        return null;
    }
}
