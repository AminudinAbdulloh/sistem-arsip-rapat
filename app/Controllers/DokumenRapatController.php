<?php

namespace App\Controllers;

use App\Libraries\DokumenRapatService;
use App\Models\DokumenRapatModel;
use App\Models\NotulensiRapatModel;
use App\Models\UndanganRapatModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class DokumenRapatController extends BaseController
{
    protected DokumenRapatModel $model;
    protected DokumenRapatService $service;

    public function __construct()
    {
        $this->model   = new DokumenRapatModel();
        $this->service = new DokumenRapatService(null, $this->model);
    }

    public function store(int $undanganId): RedirectResponse
    {
        if (! (new UndanganRapatModel())->find($undanganId)) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak ditemukan.');
        }

        $back  = "/undangan/{$undanganId}/kelengkapan";
        $error = $this->service->simpan(
            $undanganId,
            (string) $this->request->getPost('judul'),
            $this->request->getFile('berkas'),
            (int) session()->get('user')['id'],
        );

        if ($error !== null) {
            return redirect()->to($back)->with('error', $error);
        }

        return redirect()->to($back)->with('success', 'Dokumen berhasil diunggah.');
    }

    public function delete(int $id): RedirectResponse
    {
        $dokumen = $this->model->find($id);
        if (! $dokumen) {
            return redirect()->to('/undangan')->with('error', 'Dokumen tidak ditemukan.');
        }

        $this->service->hapusBerkas($dokumen['berkas']);
        $this->model->delete($id);

        return redirect()->to("/undangan/{$dokumen['undangan_id']}/kelengkapan")->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Unduh dokumen. Admin/Sekretaris/Kaprodi boleh selalu; peran lain (Dosen)
     * hanya bila notulensi rapat tersebut sudah terverifikasi.
     */
    public function download(int $id): ResponseInterface|RedirectResponse
    {
        $dokumen = $this->model->find($id);
        if (! $dokumen) {
            return redirect()->to('/dashboard')->with('error', 'Dokumen tidak ditemukan.');
        }

        $staf = has_role('admin', 'sekretaris', 'kaprodi');
        if (! $staf && ! (new NotulensiRapatModel())->isUndanganVerified((int) $dokumen['undangan_id'])) {
            return redirect()->to('/arsip')->with('error', 'Dokumen tidak tersedia.');
        }

        $path = DokumenRapatService::path($dokumen['berkas']);
        if (! is_file($path)) {
            $tujuan = $staf ? "/undangan/{$dokumen['undangan_id']}/kelengkapan" : '/arsip';

            return redirect()->to($tujuan)->with('error', 'Berkas tidak ditemukan di server.');
        }

        return $this->response->download($path, null)->setFileName($dokumen['nama_asli']);
    }
}
