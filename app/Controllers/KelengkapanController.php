<?php

namespace App\Controllers;

use App\Models\BeritaAcaraModel;
use App\Models\DaftarHadirModel;
use App\Models\DokumenRapatModel;
use App\Models\NotulensiRapatModel;
use App\Models\UndanganRapatModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Halaman "Kelengkapan Rapat" per undangan: daftar hadir, berita acara, dan dokumen.
 */
class KelengkapanController extends BaseController
{
    public function show(int $undanganId): string|RedirectResponse
    {
        $undangan = (new UndanganRapatModel())->findByIdWithUser($undanganId);
        if (! $undangan) {
            return redirect()->to('/undangan')->with('error', 'Undangan tidak ditemukan.');
        }

        $notulensi = (new NotulensiRapatModel())->where('undangan_id', $undanganId)->first();

        return view('Kelengkapan/show', [
            'title'       => 'Kelengkapan Rapat - Arsip ITD',
            'undangan'    => $undangan,
            'notulensi'   => $notulensi,
            'hadir'       => (new DaftarHadirModel())->findByUndangan($undanganId),
            'beritaAcara' => (new BeritaAcaraModel())->findByUndangan($undanganId),
            'dokumen'     => (new DokumenRapatModel())->findByUndangan($undanganId),
        ]);
    }
}
