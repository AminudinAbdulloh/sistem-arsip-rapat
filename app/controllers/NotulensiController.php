<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/Notulensi.php';
require_once BASE_PATH . '/app/models/Undangan.php';

class NotulensiController extends Controller {
    private $model;
    private $undanganModel;

    public function __construct() {
        $this->model = new Notulensi();
        $this->undanganModel = new Undangan();
    }

    public function index($param = null) {
        $this->requireLogin();
        $notulensi = $this->model->getAll();
        $this->view('layouts/main', [
            'title' => 'Notulensi Rapat',
            'content' => 'notulensi/index',
            'notulensi' => $notulensi,
        ]);
    }

    public function create($param = null) {
        $this->requireLogin();
        $undanganList = $this->undanganModel->getAll();

        if (empty($undanganList)) {
            $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Belum ada undangan rapat. Silakan buat undangan terlebih dahulu.'];
            $this->redirect('undangan');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dokumentasi = null;
            if (!empty($_FILES['dokumentasi']['name'])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['dokumentasi']['type'], $allowedTypes)) {
                    $ext = pathinfo($_FILES['dokumentasi']['name'], PATHINFO_EXTENSION);
                    $filename = 'dok_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                    $dest = BASE_PATH . '/public/uploads/dokumentasi/' . $filename;
                    move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $dest);
                    $dokumentasi = $filename;
                } else {
                    $error = 'Format file tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.';
                }
            }

            if (!$error) {
                $data = [
                    'undangan_id' => (int)($_POST['undangan_id'] ?? 0),
                    'tgl_rapat' => $_POST['tgl_rapat'] ?? '',
                    'tema_rapat' => trim($_POST['tema_rapat'] ?? ''),
                    'deskripsi_rapat' => trim($_POST['deskripsi_rapat'] ?? ''),
                    'catatan' => trim($_POST['catatan'] ?? ''),
                    'dokumentasi' => $dokumentasi,
                    'dibuat_oleh' => $_SESSION['user_id'],
                ];
                if (!$data['undangan_id'] || empty($data['tgl_rapat']) || empty($data['tema_rapat']) || empty($data['deskripsi_rapat'])) {
                    $error = 'Semua field wajib harus diisi.';
                } else {
                    if ($this->model->create($data)) {
                        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Notulensi rapat berhasil ditambahkan.'];
                        $this->redirect('notulensi');
                    } else {
                        $error = 'Gagal menyimpan data.';
                    }
                }
            }
        }

        $this->view('layouts/main', [
            'title' => 'Tambah Notulensi Rapat',
            'content' => 'notulensi/form',
            'error' => $error,
            'undanganList' => $undanganList,
            'notulensi' => null,
        ]);
    }

    public function edit($id) {
        $this->requireLogin();
        $notulensi = $this->model->getById($id);
        if (!$notulensi) { $this->redirect('notulensi'); }

        $undanganList = $this->undanganModel->getAll();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dokumentasi = null;
            if (!empty($_FILES['dokumentasi']['name'])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($_FILES['dokumentasi']['type'], $allowedTypes)) {
                    // Delete old file
                    if ($notulensi['dokumentasi']) {
                        $old = BASE_PATH . '/public/uploads/dokumentasi/' . $notulensi['dokumentasi'];
                        if (file_exists($old)) unlink($old);
                    }
                    $ext = pathinfo($_FILES['dokumentasi']['name'], PATHINFO_EXTENSION);
                    $filename = 'dok_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                    $dest = BASE_PATH . '/public/uploads/dokumentasi/' . $filename;
                    move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $dest);
                    $dokumentasi = $filename;
                } else {
                    $error = 'Format file tidak valid.';
                }
            }

            if (!$error) {
                $data = [
                    'undangan_id' => (int)($_POST['undangan_id'] ?? 0),
                    'tgl_rapat' => $_POST['tgl_rapat'] ?? '',
                    'tema_rapat' => trim($_POST['tema_rapat'] ?? ''),
                    'deskripsi_rapat' => trim($_POST['deskripsi_rapat'] ?? ''),
                    'catatan' => trim($_POST['catatan'] ?? ''),
                    'dokumentasi' => $dokumentasi,
                ];
                if ($this->model->update($id, $data)) {
                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Notulensi rapat berhasil diperbarui.'];
                    $this->redirect('notulensi');
                } else {
                    $error = 'Gagal memperbarui data.';
                }
            }
        }

        $this->view('layouts/main', [
            'title' => 'Edit Notulensi Rapat',
            'content' => 'notulensi/form',
            'error' => $error,
            'undanganList' => $undanganList,
            'notulensi' => $notulensi,
        ]);
    }

    public function delete($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Notulensi rapat berhasil dihapus.'];
        }
        $this->redirect('notulensi');
    }

    public function detail($id) {
        $this->requireLogin();
        $notulensi = $this->model->getById($id);
        if (!$notulensi) { $this->redirect('notulensi'); }

        $this->view('layouts/main', [
            'title' => 'Detail Notulensi Rapat',
            'content' => 'notulensi/detail',
            'notulensi' => $notulensi,
        ]);
    }
}