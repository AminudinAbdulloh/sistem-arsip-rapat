<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Edit Notulensi Rapat</h3>

        <?php
        // Decode dokumentasi (backward-compatible)
        $existingFotos = [];
        if (!empty($notulensi['dokumentasi'])) {
            $decoded = json_decode($notulensi['dokumentasi'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $existingFotos = $decoded;
            } else {
                $existingFotos = [$notulensi['dokumentasi']];
            }
        }
        ?>

        <form action="/notulensi/<?= $notulensi['id'] ?>/update" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Undangan Rapat <span class="text-red-500">*</span></label>
                <select name="undangan_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                    <?php foreach ($undangan as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $u['id'] == $notulensi['undangan_id'] ? 'selected' : '' ?>>
                            <?= date('d/m/Y', strtotime($u['waktu'])) ?> - <?= esc($u['acara']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Rapat <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_rapat" required value="<?= $notulensi['tgl_rapat'] ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tema Rapat <span class="text-red-500">*</span></label>
                <input type="text" name="tema_rapat" required value="<?= esc($notulensi['tema_rapat']) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Rapat <span class="text-red-500">*</span></label>
                <textarea name="deskripsi_rapat" required rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none"><?= esc($notulensi['deskripsi_rapat']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="catatan" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none"><?= esc($notulensi['catatan']) ?></textarea>
            </div>

            <!-- Dokumentasi Foto -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-camera mr-1"></i>Dokumentasi (Foto)
                </label>

                <!-- Foto yang sudah ada -->
                <?php if (!empty($existingFotos)): ?>
                    <p class="text-xs text-gray-500 mb-2">
                        Foto saat ini &mdash; centang <span class="text-red-500 font-semibold">Hapus</span> untuk menghapus:
                    </p>
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <?php foreach ($existingFotos as $foto): ?>
                            <div class="existing-item relative rounded-xl overflow-hidden border-2 border-transparent transition-all duration-200"
                                id="wrap-<?= esc($foto) ?>">
                                <img src="/uploads/dokumentasi/<?= esc($foto) ?>"
                                    alt="Dokumentasi"
                                    class="w-full aspect-square object-cover">
                                <label class="absolute top-2 right-2 flex items-center gap-1 bg-white/90 rounded-md px-1.5 py-0.5 cursor-pointer shadow text-xs text-red-600 font-medium select-none">
                                    <input type="checkbox" name="hapus_foto[]"
                                        value="<?= esc($foto) ?>"
                                        id="del-<?= esc($foto) ?>"
                                        onchange="toggleMarkDelete('<?= esc($foto) ?>')"
                                        class="accent-red-500">
                                    Hapus
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Tambah Foto Baru -->
                <p class="text-xs text-gray-500 mb-1">Tambah foto baru:</p>
                <label for="fotoInput"
                    class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-5 cursor-pointer hover:border-[#1e3a5f] hover:bg-blue-50 transition-all duration-200"
                    id="dropLabel">
                    <i class="fas fa-plus-circle text-2xl text-gray-400 mb-1"></i>
                    <p class="text-sm text-gray-500">Klik untuk pilih foto baru</p>
                    <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, GIF, WEBP &bull; Bisa pilih lebih dari 1</p>
                </label>
                <input type="file" id="fotoInput" name="dokumentasi[]"
                    accept="image/*" multiple
                    class="hidden"
                    onchange="showNewPreviews(this)">

                <!-- Preview foto baru -->
                <div id="newPreviewGrid" class="grid grid-cols-3 gap-3 mt-3"></div>
                <p id="fotoCount" class="text-xs text-gray-500 mt-2 hidden"></p>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="/notulensi" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    #dropLabel.has-files { border-color: #1e3a5f; background-color: #eff6ff; }
    .existing-item.marked-delete { border-color: #ef4444; opacity: 0.5; }
    .new-preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #f3f4f6;
    }
    .new-preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .new-preview-item .badge {
        position: absolute; bottom: 4px; right: 4px;
        background: rgba(0,0,0,0.5); color: #fff;
        font-size: 10px; border-radius: 4px; padding: 1px 5px;
    }
</style>

<script>
function toggleMarkDelete(nama) {
    var cb   = document.getElementById('del-' + nama);
    var wrap = document.getElementById('wrap-' + nama);
    if (cb && wrap) {
        wrap.classList.toggle('marked-delete', cb.checked);
    }
}

function showNewPreviews(input) {
    var grid    = document.getElementById('newPreviewGrid');
    var countEl = document.getElementById('fotoCount');
    var label   = document.getElementById('dropLabel');
    var files   = input.files;

    grid.innerHTML = '';

    if (!files || files.length === 0) {
        label.classList.remove('has-files');
        countEl.classList.add('hidden');
        return;
    }

    label.classList.add('has-files');
    countEl.textContent = files.length + ' foto baru dipilih';
    countEl.classList.remove('hidden');

    Array.from(files).forEach(function(file, i) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var item = document.createElement('div');
            item.className = 'new-preview-item';
            item.innerHTML =
                '<img src="' + e.target.result + '" alt="' + file.name + '">' +
                '<span class="badge">' + (i + 1) + '</span>';
            grid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
}
</script>
<?= $this->endSection() ?>
