<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/Notulensi.php';
require_once BASE_PATH . '/app/models/Undangan.php';

class NotulensiController extends Controller {
    private $model;
    private $undanganModel;

    // Direktori upload
    const DIR_FOTO    = '/public/uploads/dokumentasi/';
    const DIR_DOKUMEN = '/public/uploads/dokumen/';

    // Tipe yang diizinkan
    const ALLOWED_FOTO    = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const ALLOWED_DOKUMEN = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB per file

    public function __construct() {
        $this->model = new Notulensi();
        $this->undanganModel = new Undangan();

        // Pastikan direktori upload ada
        foreach ([self::DIR_FOTO, self::DIR_DOKUMEN] as $dir) {
            $path = BASE_PATH . $dir;
            if (!is_dir($path)) mkdir($path, 0755, true);
        }
    }

    // -------------------------------------------------------
    // INDEX
    // -------------------------------------------------------
    public function index($param = null) {
        $this->requireLogin();
        $notulensi = $this->model->getAll();
        $this->view('layouts/main', [
            'title'     => 'Notulensi Rapat',
            'content'   => 'notulensi/index',
            'notulensi' => $notulensi,
        ]);
    }

    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------
    public function create($param = null) {
        $this->requireLogin();
        $undanganList = $this->undanganModel->getUndanganTanpaNotulensi();

        if (empty($undanganList)) {
            $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Semua undangan rapat sudah memiliki notulensi, atau belum ada undangan. Silakan buat undangan terlebih dahulu.'];
            $this->redirect('notulensi');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $undanganId = (int)($_POST['undangan_id'] ?? 0);
            $undanganData = $this->undanganModel->getById($undanganId);

            if (!$undanganData) {
                $error = 'Undangan rapat tidak ditemukan.';
            } elseif (strtotime($undanganData['waktu']) > time()) {
                $error = 'Notulensi tidak dapat dibuat sebelum rapat dimulai. Rapat dijadwalkan pada ' . date('d/m/Y H:i', strtotime($undanganData['waktu'])) . '.';
            } elseif ($this->model->existsByUndanganId($undanganId)) {
                $error = 'Undangan rapat ini sudah memiliki notulensi.';
            } else {
                $deskripsi = trim($_POST['deskripsi_rapat'] ?? '');
                $catatan   = trim($_POST['catatan'] ?? '');

                if (!$undanganId || empty($deskripsi)) {
                    $error = 'Pilih undangan rapat dan isi deskripsi rapat.';
                } else {
                    // Simpan data utama dulu
                    $notulensiId = $this->model->create([
                        'undangan_id'     => $undanganId,
                        'deskripsi_rapat' => $deskripsi,
                        'catatan'         => $catatan,
                        'dibuat_oleh'     => $_SESSION['user_id'],
                    ]);

                    if (!$notulensiId) {
                        $error = 'Gagal menyimpan data.';
                    } else {
                        // Upload multiple foto dokumentasi
                        $fotoError = $this->uploadFotoMultiple($notulensiId, 'dokumentasi');
                        // Upload multiple dokumen pendukung
                        $dokError  = $this->uploadDokumenMultiple($notulensiId, 'dokumen_pendukung');

                        $uploadError = $fotoError ?: $dokError;
                        if ($uploadError) {
                            // Data tersimpan tapi ada error upload file
                            $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Notulensi tersimpan, namun ada file yang gagal diupload: ' . $uploadError];
                        } else {
                            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Notulensi rapat berhasil ditambahkan.'];
                        }
                        $this->redirect('notulensi');
                    }
                }
            }
        }

        $this->view('layouts/main', [
            'title'        => 'Tambah Notulensi Rapat',
            'content'      => 'notulensi/form',
            'error'        => $error,
            'undanganList' => $undanganList,
            'notulensi'    => null,
        ]);
    }

    // -------------------------------------------------------
    // EDIT
    // -------------------------------------------------------
    public function edit($id) {
        $this->requireLogin();
        $notulensi = $this->model->getById($id);
        if (!$notulensi) { $this->redirect('notulensi'); }

        $undanganList = $this->undanganModel->getAll();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $undanganId = (int)($_POST['undangan_id'] ?? 0);

            if ($undanganId !== (int)$notulensi['undangan_id'] && $this->model->existsByUndanganId($undanganId)) {
                $error = 'Undangan rapat yang dipilih sudah memiliki notulensi lain.';
            } else {
                $deskripsi = trim($_POST['deskripsi_rapat'] ?? '');
                $catatan   = trim($_POST['catatan'] ?? '');

                if ($this->model->update($id, [
                    'undangan_id'     => $undanganId,
                    'deskripsi_rapat' => $deskripsi,
                    'catatan'         => $catatan,
                ])) {
                    // Hapus foto yang dipilih user untuk dihapus
                    $hapusFoto = $_POST['hapus_dokumentasi'] ?? [];
                    foreach ($hapusFoto as $fotoId) {
                        $this->model->deleteDokumentasi((int)$fotoId);
                    }

                    // Hapus dokumen yang dipilih user untuk dihapus
                    $hapusDok = $_POST['hapus_dokumen'] ?? [];
                    foreach ($hapusDok as $dokId) {
                        $this->model->deleteDokumen((int)$dokId);
                    }

                    // Upload foto baru
                    $fotoError = $this->uploadFotoMultiple($id, 'dokumentasi');
                    // Upload dokumen baru
                    $dokError  = $this->uploadDokumenMultiple($id, 'dokumen_pendukung');

                    $uploadError = $fotoError ?: $dokError;
                    if ($uploadError) {
                        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Notulensi diperbarui, namun ada file yang gagal diupload: ' . $uploadError];
                    } else {
                        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Notulensi rapat berhasil diperbarui.'];
                    }
                    $this->redirect('notulensi');
                } else {
                    $error = 'Gagal memperbarui data.';
                }
            }
        }

        // Reload dengan file terbaru
        $notulensi = $this->model->getById($id);
        $this->view('layouts/main', [
            'title'        => 'Edit Notulensi Rapat',
            'content'      => 'notulensi/form',
            'error'        => $error,
            'undanganList' => $undanganList,
            'notulensi'    => $notulensi,
        ]);
    }

    // -------------------------------------------------------
    // DELETE
    // -------------------------------------------------------
    public function delete($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Hapus semua file terkait
            $this->model->deleteAllDokumentasi($id);
            $this->model->deleteAllDokumen($id);
            $this->model->delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Notulensi rapat berhasil dihapus.'];
        }
        $this->redirect('notulensi');
    }

    // -------------------------------------------------------
    // DETAIL
    // -------------------------------------------------------
    public function detail($id) {
        $this->requireLogin();
        $notulensi = $this->model->getById($id);
        if (!$notulensi) { $this->redirect('notulensi'); }

        $this->view('layouts/main', [
            'title'     => 'Detail Notulensi Rapat',
            'content'   => 'notulensi/detail',
            'notulensi' => $notulensi,
        ]);
    }

    // -------------------------------------------------------
    // DELETE SINGLE FOTO (AJAX / redirect)
    // -------------------------------------------------------
    public function deleteFoto($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->deleteDokumentasi((int)$id);
        }
        // Redirect kembali ke referer atau notulensi
        $ref = $_SERVER['HTTP_REFERER'] ?? null;
        if ($ref) { header('Location: ' . $ref); exit; }
        $this->redirect('notulensi');
    }

    // -------------------------------------------------------
    // DELETE SINGLE DOKUMEN (AJAX / redirect)
    // -------------------------------------------------------
    public function deleteDokumen($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->deleteDokumen((int)$id);
        }
        $ref = $_SERVER['HTTP_REFERER'] ?? null;
        if ($ref) { header('Location: ' . $ref); exit; }
        $this->redirect('notulensi');
    }

    // -------------------------------------------------------
    // HELPER: Upload multiple foto
    // -------------------------------------------------------
    private function uploadFotoMultiple($notulensiId, $inputName) {
        if (empty($_FILES[$inputName]['name'][0])) return null;

        $files = $_FILES[$inputName];
        $errors = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if (empty($files['name'][$i])) continue;
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = $files['name'][$i] . ' (error upload)';
                continue;
            }
            if ($files['size'][$i] > self::MAX_FILE_SIZE) {
                $errors[] = $files['name'][$i] . ' (ukuran melebihi 10MB)';
                continue;
            }
            if (!in_array($files['type'][$i], self::ALLOWED_FOTO)) {
                $errors[] = $files['name'][$i] . ' (format tidak didukung)';
                continue;
            }
            $ext      = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $filename = 'dok_' . time() . '_' . $notulensiId . '_' . rand(1000, 9999) . '.' . $ext;
            $dest     = BASE_PATH . self::DIR_FOTO . $filename;
            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $this->model->addDokumentasi($notulensiId, $filename);
            } else {
                $errors[] = $files['name'][$i] . ' (gagal disimpan)';
            }
        }
        return empty($errors) ? null : implode(', ', $errors);
    }

    // -------------------------------------------------------
    // HELPER: Upload multiple dokumen pendukung
    // -------------------------------------------------------
    private function uploadDokumenMultiple($notulensiId, $inputName) {
        if (empty($_FILES[$inputName]['name'][0])) return null;

        $files = $_FILES[$inputName];
        $errors = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if (empty($files['name'][$i])) continue;
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = $files['name'][$i] . ' (error upload)';
                continue;
            }
            if ($files['size'][$i] > self::MAX_FILE_SIZE) {
                $errors[] = $files['name'][$i] . ' (ukuran melebihi 10MB)';
                continue;
            }
            if (!in_array($files['type'][$i], self::ALLOWED_DOKUMEN)) {
                $errors[] = $files['name'][$i] . ' (format tidak didukung)';
                continue;
            }
            $ext          = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $filename     = 'dukung_' . time() . '_' . $notulensiId . '_' . rand(1000, 9999) . '.' . $ext;
            $originalName = $files['name'][$i];
            $mimeType     = $files['type'][$i];
            $dest         = BASE_PATH . self::DIR_DOKUMEN . $filename;
            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $this->model->addDokumen($notulensiId, $filename, $originalName, $mimeType);
            } else {
                $errors[] = $originalName . ' (gagal disimpan)';
            }
        }
        return empty($errors) ? null : implode(', ', $errors);
    }

    // -------------------------------------------------------
    // HELPER: Ikon file berdasarkan mime type
    // -------------------------------------------------------
    public static function fileIcon($mimeType) {
        if (str_contains($mimeType, 'pdf'))         return 'fa-file-pdf';
        if (str_contains($mimeType, 'word'))        return 'fa-file-word';
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) return 'fa-file-excel';
        if (str_contains($mimeType, 'presentation') || str_contains($mimeType, 'powerpoint')) return 'fa-file-powerpoint';
        return 'fa-file-alt';
    }
}