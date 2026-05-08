<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Edit Undangan Rapat</h3>

        <form action="/undangan/<?= $undangan['id'] ?>/update" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hari <span class="text-red-500">*</span></label>
                <select name="hari" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                    <?php $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; ?>
                    <?php foreach ($hariList as $h): ?>
                        <option value="<?= $h ?>" <?= $h == $undangan['hari'] ? 'selected' : '' ?>><?= $h ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="waktu" required
                    value="<?= date('Y-m-d\TH:i', strtotime($undangan['waktu'])) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat <span class="text-red-500">*</span></label>
                <input type="text" name="tempat" required value="<?= esc($undangan['tempat']) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Acara <span class="text-red-500">*</span></label>
                <textarea name="acara" required rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none"><?= esc($undangan['acara']) ?></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="/undangan" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
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
