<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Edit Notulensi Rapat</h3>

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

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dokumentasi (Foto)</label>
                <?php if ($notulensi['dokumentasi']): ?>
                    <div class="mb-2">
                        <img src="/uploads/dokumentasi/<?= $notulensi['dokumentasi'] ?>" alt="Dokumentasi" class="w-32 h-32 object-cover rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">Foto saat ini</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="dokumentasi" accept="image/*"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah foto. Format: JPG, PNG, GIF, WEBP.</p>
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
<?= $this->endSection() ?>
