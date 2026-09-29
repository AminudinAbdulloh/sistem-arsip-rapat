<?php

namespace App\Controllers;

use App\Models\DaftarHadirModel;
use App\Models\UndanganRapatModel;
use CodeIgniter\HTTP\RedirectResponse;

class DaftarHadirController extends BaseController
{
    protected DaftarHadirModel $model;

    public function __construct()
    {
        $this->model = new DaftarHadirModel();
    }

    public function store(int $undanganId): RedirectResponse
    {
        if (! (new UndanganRapatModel())->find($undanganId)) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak ditemukan.');
        }

        $back = "/undangan/{$undanganId}/kelengkapan";
        $data = [
            'nama'       => trim($this->request->getPost('nama') ?? ''),
            'jabatan'    => trim($this->request->getPost('jabatan') ?? ''),
            'status'     => $this->request->getPost('status') ?? '',
            'keterangan' => trim($this->request->getPost('keterangan') ?? ''),
        ];

        if (! $this->validateData($data, $this->model->rules())) {
            return redirect()->to($back)->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->model->insert([
            'undangan_id' => $undanganId,
            'nama'        => $data['nama'],
            'jabatan'     => $data['jabatan'] !== '' ? $data['jabatan'] : null,
            'status'      => $data['status'],
            'keterangan'  => $data['keterangan'] !== '' ? $data['keterangan'] : null,
        ]);

        return redirect()->to($back)->with('success', 'Peserta berhasil ditambahkan ke daftar hadir.');
    }

    public function delete(int $id): RedirectResponse
    {
        $peserta = $this->model->find($id);
        if (! $peserta) {
            return redirect()->to('/undangan')->with('error', 'Data peserta tidak ditemukan.');
        }

        $this->model->delete($id);

        return redirect()->to("/undangan/{$peserta['undangan_id']}/kelengkapan")->with('success', 'Peserta berhasil dihapus dari daftar hadir.');
    }
}
