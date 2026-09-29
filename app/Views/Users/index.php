<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Pengguna</h3>
        <a href="/users/create" class="bg-[#1e3a5f] text-white px-4 py-2 rounded-lg hover:bg-[#2d5f8f] transition-colors">
            <i class="fas fa-plus mr-2"></i>Tambah Pengguna
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-2"></i>
                            <p>Belum ada pengguna</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $i => $u): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $i + 1 ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($u['nip']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($u['nama']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($u['jabatan'] ?? '') ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= esc(\App\Models\UserModel::ROLE_LABELS[$u['role']] ?? $u['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <a href="/users/<?= $u['id'] ?>/edit" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ((int) $u['id'] !== (int) (session()->get('user')['id'] ?? 0)): ?>
                                        <form action="/users/<?= $u['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
