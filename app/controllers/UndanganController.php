<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/Undangan.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class UndanganController extends Controller
{
    private Undangan $model;

    public function __construct()
    {
        $this->model = new Undangan();
    }

    // ----------------------------------------------------------------
    // CRUD
    // ----------------------------------------------------------------

    public function index(): void
    {
        $this->requireLogin();

        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $paginate = $this->model->getPaginated($page);

        $this->renderMain('Undangan Rapat', 'undangan/index', [
            'undangan'    => $paginate['data'],
            'currentPage' => $paginate['page'],
            'totalPages'  => $paginate['totalPages'],
            'total'       => $paginate['total'],
            'baseUrl'     => BASE_URL,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $error = '';

        if ($this->isPost()) {
            $data  = $this->buildFormData();
            $error = $this->validateFormData($data);

            if (!$error) {
                $data['dibuat_oleh'] = $_SESSION['user_id'];
                if ($this->model->create($data)) {
                    $this->flashSuccess('Undangan rapat berhasil ditambahkan.');
                    $this->redirect('undangan');
                } else {
                    $error = 'Gagal menyimpan data.';
                }
            }
        }

        $this->renderMain('Tambah Undangan Rapat', 'undangan/form', [
            'error'    => $error,
            'undangan' => null,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $undangan = $this->findOrRedirect((int) $id);
        $error    = '';

        if ($this->isPost()) {
            $data  = $this->buildFormData();
            $error = $this->validateFormData($data);

            if (!$error) {
                if ($this->model->update((int) $id, $data)) {
                    $this->flashSuccess('Undangan rapat berhasil diperbarui.');
                    $this->redirect('undangan');
                } else {
                    $error = 'Gagal memperbarui data.';
                }
            }
        }

        $this->renderMain('Edit Undangan Rapat', 'undangan/form', [
            'error'    => $error,
            'undangan' => $undangan,
        ]);
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        if ($this->isPost()) {
            $this->model->delete((int) $id);
            $this->flashSuccess('Undangan rapat berhasil dihapus.');
        }
        $this->redirect('undangan');
    }

    // ----------------------------------------------------------------
    // Generate dokumen undangan (.docx)
    // ----------------------------------------------------------------

    public function doc(string $id): void
    {
        $this->requireLogin();
        $u = $this->findOrRedirect((int) $id);

        $templatePath = BASE_PATH . '/public/templates/undangan_template.docx';
        if (!file_exists($templatePath)) {
            http_response_code(404);
            exit('Template undangan tidak ditemukan di: ' . $templatePath);
        }

        $replacements = [
            '{{PERIHAL}}'   => $u['acara'],
            '{{HARI_TGL}}'  => DateHelper::tanggalSurat($u['waktu']),
            '{{WAKTU}}'     => DateHelper::jamSelesai($u['waktu']),
            '{{TEMPAT}}'    => $u['tempat'],
            '{{TGL_SURAT}}' => DateHelper::tanggal($u['tgl_surat']),
        ];

        $filename = 'Undangan ' . $u['acara'] . ' ' . DateHelper::tanggal($u['waktu']) . '.docx';
        $this->generateDocx($templatePath, $replacements, $filename);
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private function findOrRedirect(int $id): array
    {
        $undangan = $this->model->getById($id);
        if (!$undangan) {
            $this->redirect('undangan');
        }
        return $undangan;
    }

    private function buildFormData(): array
    {
        return [
            'waktu'     => $this->input('waktu'),
            'tempat'    => $this->trimInput('tempat'),
            'acara'     => $this->trimInput('acara'),
            'tgl_surat' => $this->input('tgl_surat', date('Y-m-d')),
        ];
    }

    private function validateFormData(array $data): string
    {
        if (empty($data['waktu']) || empty($data['tempat']) || empty($data['acara'])) {
            return 'Semua field harus diisi.';
        }
        return '';
    }

    private function generateDocx(string $templatePath, array $replacements, string $outputFilename): void
    {
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('undangan_') . '.docx';
        copy($templatePath, $tmpFile);

        $zip = new ZipArchive();
        if ($zip->open($tmpFile) !== true) {
            exit('Gagal membuka template DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            exit('Gagal membaca isi template.');
        }

        foreach ($replacements as $placeholder => $value) {
            $xml = str_replace(htmlspecialchars($placeholder), htmlspecialchars($value), $xml);
            $xml = str_replace($placeholder, htmlspecialchars($value), $xml);
        }

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $outputFilename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }
}