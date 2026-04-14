<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/Notulensi.php';
require_once BASE_PATH . '/app/models/Undangan.php';
require_once BASE_PATH . '/app/helpers/FileUploadHelper.php';

class NotulensiController extends Controller
{
    private Notulensi $model;
    private Undangan  $undanganModel;

    private const DIR_FOTO    = '/public/uploads/dokumentasi/';
    private const DIR_DOKUMEN = '/public/uploads/dokumen/';

    public function __construct()
    {
        $this->model         = new Notulensi();
        $this->undanganModel = new Undangan();
    }

    // ----------------------------------------------------------------
    // CRUD
    // ----------------------------------------------------------------

    public function index(): void
    {
        $this->requireLogin();

        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $paginate = $this->model->getPaginated($page);

        $this->renderMain('Notulensi Rapat', 'notulensi/index', [
            'notulensi'   => $paginate['data'],
            'currentPage' => $paginate['page'],
            'totalPages'  => $paginate['totalPages'],
            'total'       => $paginate['total'],
            'baseUrl'     => BASE_URL,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();

        $undanganList = $this->undanganModel->getUndanganTanpaNotulensi();
        if (empty($undanganList)) {
            $this->flashWarning('Semua undangan rapat sudah memiliki notulensi, atau belum ada undangan. Silakan buat undangan terlebih dahulu.');
            $this->redirect('notulensi');
        }

        $error = '';

        if ($this->isPost()) {
            $this->verifyCsrfToken();
            $undanganId = (int) $this->input('undangan_id', 0);
            $error      = $this->validateUndangan($undanganId, isNew: true);

            if (!$error) {
                $id = $this->model->create($this->buildNotulensiData($undanganId));
                if (!$id) {
                    $error = 'Gagal menyimpan data.';
                } else {
                    $uploadErrors = $this->uploadAllFiles($id);
                    $this->flashAfterUpload('Notulensi rapat berhasil ditambahkan.', $uploadErrors);
                    $this->redirect('notulensi');
                }
            }
        }

        $this->renderMain('Tambah Notulensi Rapat', 'notulensi/form', [
            'error'        => $error,
            'undanganList' => $undanganList,
            'notulensi'    => null,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $notulensi    = $this->findOrRedirect((int) $id);
        $undanganList = $this->undanganModel->getAll();
        $error        = '';

        if ($this->isPost()) {
            $this->verifyCsrfToken();
            $undanganId = (int) $this->input('undangan_id', 0);
            $error      = $this->validateUndangan($undanganId, isNew: false, currentId: (int) $notulensi['undangan_id']);

            if (!$error) {
                if ($this->model->update((int) $id, $this->buildNotulensiData($undanganId))) {
                    $this->deleteCheckedFiles((int) $id);
                    $uploadErrors = $this->uploadAllFiles((int) $id);
                    $this->flashAfterUpload('Notulensi rapat berhasil diperbarui.', $uploadErrors);
                    $this->redirect('notulensi');
                } else {
                    $error = 'Gagal memperbarui data.';
                }
            }
        }

        $this->renderMain('Edit Notulensi Rapat', 'notulensi/form', [
            'error'        => $error,
            'undanganList' => $undanganList,
            'notulensi'    => $this->model->getById((int) $id),
        ]);
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        if ($this->isPost()) {
            $this->verifyCsrfToken();
            $intId = (int) $id;
            $this->model->deleteAllDokumentasi($intId);
            $this->model->deleteAllDokumen($intId);
            $this->model->delete($intId);
            $this->flashSuccess('Notulensi rapat berhasil dihapus.');
        }
        $this->redirect('notulensi');
    }

    public function detail(string $id): void
    {
        $this->requireLogin();
        $this->renderMain('Detail Notulensi Rapat', 'notulensi/detail', [
            'notulensi' => $this->findOrRedirect((int) $id),
        ]);
    }

    // ----------------------------------------------------------------
    // Hapus file individual (via POST)
    // ----------------------------------------------------------------

    public function deleteFoto(string $id): void
    {
        $this->requireLogin();
        if ($this->isPost()) {
            $this->verifyCsrfToken();
            $this->model->deleteDokumentasi((int) $id);
        }
        $this->redirectToReferer();
    }

    public function deleteDokumen(string $id): void
    {
        $this->requireLogin();
        if ($this->isPost()) {
            $this->verifyCsrfToken();
            $this->model->deleteDokumen((int) $id);
        }
        $this->redirectToReferer();
    }

    // ----------------------------------------------------------------
    // Private helpers — validasi
    // ----------------------------------------------------------------

    private function validateUndangan(int $undanganId, bool $isNew, int $currentId = 0): string
    {
        $undangan = $this->undanganModel->getById($undanganId);

        if (!$undangan) {
            return 'Undangan rapat tidak ditemukan.';
        }

        if ($isNew && strtotime($undangan['waktu']) > time()) {
            return 'Notulensi tidak dapat dibuat sebelum rapat dimulai. Rapat dijadwalkan pada '
                . date('d/m/Y H:i', strtotime($undangan['waktu'])) . '.';
        }

        if ($undanganId !== $currentId && $this->model->existsByUndanganId($undanganId)) {
            return 'Undangan rapat yang dipilih sudah memiliki notulensi.';
        }

        return '';
    }

    private function buildNotulensiData(int $undanganId): array
    {
        return [
            'undangan_id'     => $undanganId,
            'deskripsi_rapat' => $this->trimInput('deskripsi_rapat'),
            'catatan'         => $this->trimInput('catatan'),
            'dibuat_oleh'     => $_SESSION['user_id'],
        ];
    }

    // ----------------------------------------------------------------
    // Private helpers — upload
    // ----------------------------------------------------------------

    private function uploadAllFiles(int $notulensiId): string
    {
        $fotoErrors = FileUploadHelper::uploadMultiple(
            inputName:    'dokumentasi',
            destDir:      BASE_PATH . self::DIR_FOTO,
            prefix:       'dok',
            allowedMimes: FileUploadHelper::ALLOWED_IMAGE,
            maxSize:      FileUploadHelper::MAX_SIZE,
            onSuccess:    fn($filename) => $this->model->addDokumentasi($notulensiId, $filename)
        );

        $dokErrors = FileUploadHelper::uploadMultiple(
            inputName:    'dokumen_pendukung',
            destDir:      BASE_PATH . self::DIR_DOKUMEN,
            prefix:       'dukung',
            allowedMimes: FileUploadHelper::ALLOWED_DOCUMENT,
            maxSize:      FileUploadHelper::MAX_SIZE,
            onSuccess:    fn($filename, $originalName, $mimeType)
                              => $this->model->addDokumen($notulensiId, $filename, $originalName, $mimeType)
        );

        $all = array_merge($fotoErrors, $dokErrors);
        return empty($all) ? '' : implode(', ', $all);
    }

    private function deleteCheckedFiles(int $notulensiId): void
    {
        foreach ($_POST['hapus_dokumentasi'] ?? [] as $fotoId) {
            $this->model->deleteDokumentasi((int) $fotoId);
        }
        foreach ($_POST['hapus_dokumen'] ?? [] as $dokId) {
            $this->model->deleteDokumen((int) $dokId);
        }
    }

    // ----------------------------------------------------------------
    // Private helpers — lain-lain
    // ----------------------------------------------------------------

    private function findOrRedirect(int $id): array
    {
        $notulensi = $this->model->getById($id);
        if (!$notulensi) {
            $this->redirect('notulensi');
        }
        return $notulensi;
    }

    private function flashAfterUpload(string $successMsg, string $uploadErrors): void
    {
        if ($uploadErrors) {
            $this->flashWarning($successMsg . ' Namun ada file yang gagal diupload: ' . $uploadErrors);
        } else {
            $this->flashSuccess($successMsg);
        }
    }

    private function redirectToReferer(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer && str_starts_with($referer, BASE_URL)) {
            header('Location: ' . $referer);
            exit;
        }
        $this->redirect('notulensi');
    }
}