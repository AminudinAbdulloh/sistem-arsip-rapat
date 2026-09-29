<?php $isSelf = (int) $user['id'] === (int) (session()->get('user')['id'] ?? 0); ?>
<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Edit Pengguna</h3>

        <form action="/users/<?= $user['id'] ?>/update" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                <input type="text" name="nip" required maxlength="20" value="<?= esc(old('nip', $user['nip'])) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required maxlength="100" value="<?= esc(old('nama', $user['nama'])) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" maxlength="100" value="<?= esc(old('jabatan', $user['jabatan'] ?? '')) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                <?php if ($isSelf): ?>
                    <input type="hidden" name="role" value="<?= esc($user['role']) ?>">
                    <p class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                        <?= esc(\App\Models\UserModel::ROLE_LABELS[$user['role']] ?? $user['role']) ?>
                        <span class="text-xs text-gray-500">(role akun sendiri tidak dapat diubah)</span>
                    </p>
                <?php else: ?>
                    <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                        <?php foreach (\App\Models\UserModel::ROLE_LABELS as $value => $label): ?>
                            <option value="<?= $value ?>" <?= old('role', $user['role']) === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                <input type="password" name="kata_sandi" minlength="8" autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah. Minimal 8 karakter.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="/users" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
