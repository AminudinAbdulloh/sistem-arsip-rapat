<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="/arsip" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Kunci</label>
                <input type="text" name="q" value="<?= esc($filters['q']) ?>" placeholder="Acara, tempat, atau isi notulensi"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="<?= esc($filters['dari']) ?>"
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="<?= esc($filters['sampai']) ?>"
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>
            <button type="submit" class="bg-[#1e3a5f] text-white px-4 py-2 rounded-lg hover:bg-[#2d5f8f] transition-colors">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            <?php if ($filters['q'] !== '' || $filters['dari'] !== '' || $filters['sampai'] !== ''): ?>
                <a href="/arsip" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acara</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tempat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($arsip)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-2"></i>
                            <p>Tidak ada arsip rapat yang ditemukan</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($arsip as $i => $a): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $i + 1 ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= date('d/m/Y', strtotime($a['waktu_undangan'])) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate"><?= esc($a['nama_undangan']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($a['tempat']) ?></td>
                            <td class="px-6 py-4 text-sm">
                                <a href="/arsip/<?= $a['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
