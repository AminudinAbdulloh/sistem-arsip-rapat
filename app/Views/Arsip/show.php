<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<?php
$fotos = [];
if (!empty($arsip['dokumentasi'])) {
    $decoded = json_decode($arsip['dokumentasi'], true);
    $fotos   = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [$arsip['dokumentasi']];
}
?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Detail Arsip Rapat</h3>
            <a href="/arsip" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Acara</p>
                <p class="font-medium"><?= esc($arsip['nama_undangan']) ?></p>
                <p class="text-sm text-gray-600 mt-2">
                    <i class="far fa-calendar mr-1"></i><?= esc($arsip['hari']) ?>, <?= date('d/m/Y H:i', strtotime($arsip['waktu_undangan'])) ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-map-marker-alt mr-1"></i><?= esc($arsip['tempat']) ?>
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-2">Deskripsi Rapat</p>
                <div class="prose max-w-none"><?= nl2br(esc($arsip['deskripsi_rapat'])) ?></div>
            </div>

            <?php if (!empty($arsip['catatan'])): ?>
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <p class="text-sm text-yellow-700 font-medium mb-1"><i class="fas fa-sticky-note mr-2"></i>Catatan</p>
                    <p class="text-gray-700"><?= nl2br(esc($arsip['catatan'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($fotos)): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-3"><i class="fas fa-camera mr-2"></i>Dokumentasi Foto</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($fotos as $i => $foto): ?>
                            <a href="/uploads/dokumentasi/<?= esc($foto) ?>" target="_blank" rel="noopener" class="block aspect-square rounded-lg overflow-hidden shadow-sm">
                                <img src="/uploads/dokumentasi/<?= esc($foto) ?>" alt="Dokumentasi <?= $i + 1 ?>" class="w-full h-full object-cover">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Daftar Hadir -->
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-3"><i class="fas fa-user-check mr-2"></i>Daftar Hadir</p>
                <?php if (empty($hadir)): ?>
                    <p class="text-sm text-gray-500">Belum ada daftar hadir.</p>
                <?php else: ?>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase">
                                <th class="py-1 pr-3">No</th><th class="py-1 pr-3">Nama</th><th class="py-1 pr-3">Jabatan</th><th class="py-1">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($hadir as $i => $h): ?>
                                <tr>
                                    <td class="py-1 pr-3"><?= $i + 1 ?></td>
                                    <td class="py-1 pr-3"><?= esc($h['nama']) ?></td>
                                    <td class="py-1 pr-3"><?= esc($h['jabatan'] ?? '') ?></td>
                                    <td class="py-1"><?= esc(\App\Models\DaftarHadirModel::STATUS_LABELS[$h['status']] ?? $h['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Berita Acara -->
            <?php if ($beritaAcara): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-2"><i class="fas fa-file-signature mr-2"></i>Berita Acara
                        <?php if (!empty($beritaAcara['nomor'])): ?><span class="text-gray-800">(<?= esc($beritaAcara['nomor']) ?>)</span><?php endif; ?>
                    </p>
                    <div><?= nl2br(esc($beritaAcara['uraian'])) ?></div>
                    <?php if (!empty($beritaAcara['keputusan'])): ?>
                        <div class="mt-3 bg-green-50 rounded-lg p-3 border border-green-200">
                            <p class="text-green-700 font-medium mb-1">Keputusan</p>
                            <?= nl2br(esc($beritaAcara['keputusan'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Dokumen -->
            <?php if (!empty($dokumen)): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-3"><i class="fas fa-paperclip mr-2"></i>Dokumen Rapat</p>
                    <ul class="space-y-2">
                        <?php foreach ($dokumen as $d): ?>
                            <li>
                                <a href="/dokumen/<?= $d['id'] ?>/download" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-download mr-2"></i><?= esc($d['judul']) ?>
                                </a>
                                <span class="text-xs text-gray-500">(<?= esc($d['nama_asli']) ?>, <?= number_format($d['ukuran'] / 1024, 0, ',', '.') ?> KB)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="pt-4 border-t border-gray-200 text-sm text-gray-500">
                <p>
                    <i class="fas fa-check-circle text-green-600 mr-1"></i>Diverifikasi
                    <?php if (!empty($arsip['verified_by_nama'])): ?>oleh <span class="font-medium"><?= esc($arsip['verified_by_nama']) ?></span><?php endif; ?>
                    <?php if (!empty($arsip['verified_at'])): ?>pada <?= date('d F Y H:i', strtotime($arsip['verified_at'])) ?><?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
