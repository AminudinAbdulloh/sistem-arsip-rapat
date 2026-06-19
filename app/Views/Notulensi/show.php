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

        <?php
        // Decode dokumentasi (backward-compatible)
        $fotos = [];
        if (!empty($notulensi['dokumentasi'])) {
            $decoded = json_decode($notulensi['dokumentasi'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $fotos = $decoded;
            } else {
                $fotos = [$notulensi['dokumentasi']];
            }
        }
        ?>

        <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Undangan Terkait</p>
                <p class="font-medium"><?= esc($notulensi['nama_undangan']) ?></p>
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

            <!-- Galeri Dokumentasi -->
            <?php if (!empty($fotos)): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-3">
                        <i class="fas fa-camera mr-2"></i>Dokumentasi Foto
                        <span class="ml-1 bg-gray-200 text-gray-600 text-xs rounded-full px-2 py-0.5"><?= count($fotos) ?></span>
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($fotos as $i => $foto): ?>
                            <div class="group relative aspect-square rounded-lg overflow-hidden shadow-sm cursor-pointer"
                                onclick="openLightbox(<?= $i ?>)">
                                <img src="/uploads/dokumentasi/<?= esc($foto) ?>"
                                    alt="Dokumentasi <?= $i + 1 ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-200 flex items-center justify-center">
                                    <i class="fas fa-search-plus text-white text-xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></i>
                                </div>
                                <span class="absolute bottom-2 right-2 bg-black/50 text-white text-xs rounded px-1.5 py-0.5">
                                    <?= $i + 1 ?>/<?= count($fotos) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Lightbox -->
                <div id="lightbox" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center p-4" onclick="closeLightbox(event)">
                    <button class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10" onclick="closeLightbox()">
                        <i class="fas fa-times"></i>
                    </button>
                    <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-3xl hover:text-gray-300 z-10 bg-black/30 rounded-full w-12 h-12 flex items-center justify-center" onclick="prevPhoto(event)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-3xl hover:text-gray-300 z-10 bg-black/30 rounded-full w-12 h-12 flex items-center justify-center" onclick="nextPhoto(event)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <img id="lightboxImg" src="" alt="Dokumentasi"
                        class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain">
                    <p id="lightboxCounter" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-sm bg-black/40 rounded-full px-3 py-1"></p>
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

<?php if (!empty($fotos)): ?>
<script>
const fotos = <?= json_encode(array_map(fn($f) => '/uploads/dokumentasi/' . $f, $fotos)) ?>;
let currentIndex = 0;

function openLightbox(index) {
    currentIndex = index;
    updateLightbox();
    document.getElementById('lightbox').classList.remove('hidden');
    document.getElementById('lightbox').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(event) {
    if (event && event.target !== document.getElementById('lightbox') && event.currentTarget !== event.target) return;
    if (event && event.currentTarget && event.currentTarget.tagName === 'BUTTON') {
        // close button clicked directly
    } else if (event && event.target !== document.getElementById('lightbox')) {
        return;
    }
    document.getElementById('lightbox').classList.add('hidden');
    document.getElementById('lightbox').classList.remove('flex');
    document.body.style.overflow = '';
}

function prevPhoto(e) {
    e.stopPropagation();
    currentIndex = (currentIndex - 1 + fotos.length) % fotos.length;
    updateLightbox();
}

function nextPhoto(e) {
    e.stopPropagation();
    currentIndex = (currentIndex + 1) % fotos.length;
    updateLightbox();
}

function updateLightbox() {
    document.getElementById('lightboxImg').src = fotos[currentIndex];
    document.getElementById('lightboxCounter').textContent = (currentIndex + 1) + ' / ' + fotos.length;
}

// Keyboard navigation
document.addEventListener('keydown', e => {
    const lb = document.getElementById('lightbox');
    if (lb.classList.contains('hidden')) return;
    if (e.key === 'ArrowLeft')  { currentIndex = (currentIndex - 1 + fotos.length) % fotos.length; updateLightbox(); }
    if (e.key === 'ArrowRight') { currentIndex = (currentIndex + 1) % fotos.length; updateLightbox(); }
    if (e.key === 'Escape')     { lb.classList.add('hidden'); lb.classList.remove('flex'); document.body.style.overflow = ''; }
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
