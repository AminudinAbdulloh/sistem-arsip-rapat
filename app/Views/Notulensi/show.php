<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Detail Notulensi Rapat</h3>
            <a href="/notulensi" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Tanggal Rapat</p>
                    <p class="font-medium"><?= date('d F Y', strtotime($notulensi['tgl_rapat'])) ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Undangan Terkait</p>
                    <p class="font-medium"><?= esc($notulensi['nama_undangan']) ?></p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Tema Rapat</p>
                <p class="font-medium text-lg"><?= esc($notulensi['tema_rapat']) ?></p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-2">Deskripsi Rapat</p>
                <div class="prose max-w-none">
                    <?= nl2br(esc($notulensi['deskripsi_rapat'])) ?>
                </div>
            </div>

            <?php if ($notulensi['catatan']): ?>
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <p class="text-sm text-yellow-700 font-medium mb-1"><i class="fas fa-sticky-note mr-2"></i>Catatan</p>
                    <p class="text-gray-700"><?= nl2br(esc($notulensi['catatan'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($notulensi['dokumentasi']): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-3"><i class="fas fa-camera mr-2"></i>Dokumentasi</p>
                    <img src="/uploads/dokumentasi/<?= $notulensi['dokumentasi'] ?>" alt="Dokumentasi Rapat"
                        class="max-w-full h-auto rounded-lg shadow-md cursor-pointer hover:shadow-lg transition-shadow"
                        onclick="window.open(this.src, '_blank')">
                </div>
            <?php endif; ?>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <p>Dibuat oleh: <span class="font-medium"><?= esc($notulensi['created_by_nama']) ?></span></p>
                    <p>Pada: <?= date('d F Y H:i', strtotime($notulensi['created_at'])) ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="/notulensi/<?= $notulensi['id'] ?>/edit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
