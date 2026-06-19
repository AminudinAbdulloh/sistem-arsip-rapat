<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Tambah Notulensi Rapat</h3>

        <form action="/notulensi/store" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Undangan Rapat <span class="text-red-500">*</span></label>
                <select name="undangan_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                    <option value="">Pilih Undangan</option>
                    <?php foreach ($undangan as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= date('d/m/Y', strtotime($u['waktu'])) ?> - <?= esc($u['acara']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Rapat <span class="text-red-500">*</span></label>
                <textarea name="deskripsi_rapat" required rows="4" placeholder="Masukkan deskripsi/isi rapat"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="catatan" rows="3" placeholder="Catatan tambahan (opsional)"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none"></textarea>
            </div>

            <!-- Dokumentasi Foto (Multiple) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-camera mr-1"></i>Dokumentasi (Foto)
                </label>

                <label for="fotoInput"
                    class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-6 cursor-pointer hover:border-[#1e3a5f] hover:bg-blue-50 transition-all duration-200"
                    id="dropLabel">
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-500">Klik untuk pilih foto</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WEBP &bull; Bisa pilih lebih dari 1</p>
                </label>
                <input type="file" id="fotoInput" name="dokumentasi[]"
                    accept="image/*" multiple
                    class="hidden"
                    onchange="showPreviews(this)">

                <div id="previewGrid" class="grid grid-cols-3 gap-3 mt-3"></div>
                <p id="fotoCount" class="text-xs text-gray-500 mt-2 hidden"></p>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="/notulensi" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    #dropLabel.has-files { border-color: #1e3a5f; background-color: #eff6ff; }
    .preview-item { position: relative; aspect-ratio: 1; border-radius: 0.5rem; overflow: hidden; background: #f3f4f6; }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .preview-item .badge { position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.5); color: #fff; font-size: 10px; border-radius: 4px; padding: 1px 5px; }
</style>

<script>
function showPreviews(input) {
    var grid    = document.getElementById('previewGrid');
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
    countEl.textContent = files.length + ' foto dipilih';
    countEl.classList.remove('hidden');

    Array.from(files).forEach(function(file, i) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var item = document.createElement('div');
            item.className = 'preview-item';
            item.innerHTML = '<img src="' + e.target.result + '" alt="' + file.name + '">' +
                             '<span class="badge">' + (i + 1) + '/' + files.length + '</span>';
            grid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
}
</script>
<?= $this->endSection() ?>
