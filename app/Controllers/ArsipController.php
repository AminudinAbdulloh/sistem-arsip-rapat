<?php

namespace App\Controllers;

use App\Models\NotulensiRapatModel;
use CodeIgniter\HTTP\RedirectResponse;

class ArsipController extends BaseController
{
    protected NotulensiRapatModel $model;

    public function __construct()
    {
        $this->model = new NotulensiRapatModel();
    }

    public function index(): string
    {
        $filters = [
            'q'      => trim($this->request->getGet('q') ?? ''),
            'dari'   => $this->validDate($this->request->getGet('dari')),
            'sampai' => $this->validDate($this->request->getGet('sampai')),
        ];

        return view('Arsip/index', [
            'title'   => 'Arsip Rapat - Arsip ITD',
            'arsip'   => $this->model->searchVerified($filters),
            'filters' => $filters,
        ]);
    }

    public function show(int $id): string|RedirectResponse
    {
        $arsip = $this->model->findVerifiedById($id);
        if (! $arsip) {
            return redirect()->to('/arsip')->with('error', 'Arsip tidak ditemukan.');
        }

        return view('Arsip/show', [
            'title' => 'Detail Arsip Rapat',
            'arsip' => $arsip,
        ]);
    }

    /** Kembalikan tanggal Y-m-d yang valid, atau string kosong. */
    private function validDate(?string $value): string
    {
        $date = \DateTime::createFromFormat('Y-m-d', (string) $value);

        return ($date && $date->format('Y-m-d') === $value) ? $value : '';
    }
}
