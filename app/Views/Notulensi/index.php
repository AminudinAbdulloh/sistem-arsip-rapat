<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Notulensi Rapat</h3>
        <a href="/notulensi/create" class="bg-[#1e3a5f] text-white px-4 py-2 rounded-lg hover:bg-[#2d5f8f] transition-colors">
            <i class="fas fa-plus mr-2"></i>Tambah Notulensi
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Undangan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dibuat Oleh</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($notulensi)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Belum ada notulensi rapat</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notulensi as $i => $n): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $i + 1 ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate"><?= esc($n['nama_undangan']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($n['created_by_nama']) ?></td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <a href="/notulensi/<?= $n['id'] ?>/show" class="text-blue-600 hover:text-blue-800" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/notulensi/<?= $n['id'] ?>/edit" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="/notulensi/<?= $n['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus notulensi ini?')">
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
