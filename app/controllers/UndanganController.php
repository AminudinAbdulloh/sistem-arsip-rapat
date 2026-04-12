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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'waktu'       => $_POST['waktu'] ?? '',
                'tempat'      => trim($_POST['tempat'] ?? ''),
                'acara'       => trim($_POST['acara'] ?? ''),
                'tgl_surat'   => $_POST['tgl_surat'] ?? date('Y-m-d'),
            ];
            if (empty($data['waktu']) || empty($data['tempat']) || empty($data['acara'])) {
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
            'undangan' => null,
        ]);
    }

    public function edit($id) {
        $this->requireLogin();
        $undangan = $this->model->getById($id);
        if (!$undangan) { $this->redirect('undangan'); }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'waktu'     => $_POST['waktu'] ?? '',
                'tempat'    => trim($_POST['tempat'] ?? ''),
                'acara'     => trim($_POST['acara'] ?? ''),
                'tgl_surat' => $_POST['tgl_surat'] ?? date('Y-m-d'),
            ];
            if (empty($data['waktu']) || empty($data['tempat']) || empty($data['acara'])) {
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

    public function doc($id) {
        $this->requireLogin();
        $u = $this->model->getById($id);
        if (!$u) { $this->redirect('undangan'); }

        $templatePath = BASE_PATH . '/public/templates/undangan_template.docx';
        if (!file_exists($templatePath)) {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Template undangan tidak ditemukan di: ' . $templatePath;
            exit;
        }

        $waktu             = strtotime($u['waktu']);
        $hariIndo          = $this->getHariIndonesia(date('N', $waktu));
        $tglFormatted      = $hariIndo . ' / ' . $this->formatTanggalIndo(date('Y-m-d', $waktu));
        $jamFormatted      = date('H.i', $waktu) . ' WIB - Selesai';
        $tglSurat          = !empty($u['tgl_surat']) ? $u['tgl_surat'] : date('Y-m-d');
        $tglSuratFormatted = $this->formatTanggalIndo($tglSurat);
        $acara            = $u['acara'];

        // acara digunakan sebagai perihal surat
        $this->generateDocxFromTemplate($templatePath, [
            '{{PERIHAL}}'   => $u['acara'],
            '{{HARI_TGL}}'  => $tglFormatted,
            '{{WAKTU}}'     => $jamFormatted,
            '{{TEMPAT}}'    => $u['tempat'],
            '{{TGL_SURAT}}' => $tglSuratFormatted,
        ], 'Undangan ' . $acara . ' ' . $this->formatTanggalIndo(date('Y-m-d', $waktu)) . '.docx');
    }

    private function generateDocxFromTemplate($templatePath, $replacements, $filename) {
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('undangan_') . '.docx';
        copy($templatePath, $tmpFile);

        $zip = new ZipArchive();
        if ($zip->open($tmpFile) !== true) {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Gagal membuka template DOCX.';
            exit;
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        if ($xmlContent === false) {
            $zip->close();
            header('Content-Type: text/html; charset=utf-8');
            echo 'Gagal membaca isi template.';
            exit;
        }

        foreach ($replacements as $placeholder => $value) {
            $xmlContent = str_replace(
                htmlspecialchars($placeholder),
                htmlspecialchars($value),
                $xmlContent
            );
            $xmlContent = str_replace($placeholder, htmlspecialchars($value), $xmlContent);
        }

        $zip->addFromString('word/document.xml', $xmlContent);
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }

    private function getHariIndonesia($dayNum) {
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        return $hari[$dayNum] ?? '';
    }

    private function formatTanggalIndo($dateStr) {
        $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $ts = strtotime($dateStr);
        return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }
}