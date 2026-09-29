<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<?php
$bisaUbah = has_role('admin', 'sekretaris');
$id       = (int) $undangan['id'];
$inputCls = 'w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none';
?>
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Ringkasan rapat -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Kelengkapan Rapat</p>
                <h3 class="text-lg font-semibold text-gray-800"><?= esc($undangan['acara']) ?></h3>
                <p class="text-sm text-gray-600 mt-1">
                    <i class="far fa-calendar mr-1"></i><?= esc($undangan['hari']) ?>, <?= date('d/m/Y H:i', strtotime($undangan['waktu'])) ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-map-marker-alt mr-1"></i><?= esc($undangan['tempat']) ?>
                </p>
                <?php if ($notulensi): ?>
                    <p class="text-sm mt-2">
                        Notulensi:
                        <a href="/notulensi/<?= $notulensi['id'] ?>/show" class="text-blue-600 hover:text-blue-800">
                            <?= esc(\App\Models\NotulensiRapatModel::STATUS_LABELS[$notulensi['status_verifikasi']] ?? $notulensi['status_verifikasi']) ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            <a href="/undangan" class="text-gray-600 hover:text-gray-800 whitespace-nowrap">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Daftar hadir -->
    <div id="hadir" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h4 class="font-semibold text-gray-800 mb-4"><i class="fas fa-user-check mr-2 text-[#1e3a5f]"></i>Daftar Hadir
            <span class="ml-1 bg-gray-200 text-gray-600 text-xs rounded-full px-2 py-0.5"><?= count($hadir) ?></span>
        </h4>

        <?php if (empty($hadir)): ?>
            <p class="text-sm text-gray-500 mb-4">Belum ada daftar hadir.</p>
        <?php else: ?>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            <?php if ($bisaUbah): ?><th class="px-4 py-2"></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($hadir as $i => $h): ?>
                            <tr>
                                <td class="px-4 py-2"><?= $i + 1 ?></td>
                                <td class="px-4 py-2"><?= esc($h['nama']) ?></td>
                                <td class="px-4 py-2"><?= esc($h['jabatan'] ?? '') ?></td>
                                <td class="px-4 py-2"><?= esc(\App\Models\DaftarHadirModel::STATUS_LABELS[$h['status']] ?? $h['status']) ?></td>
                                <td class="px-4 py-2"><?= esc($h['keterangan'] ?? '') ?></td>
                                <?php if ($bisaUbah): ?>
                                    <td class="px-4 py-2 text-right">
                                        <form action="/hadir/<?= $h['id'] ?>/delete" method="POST" onsubmit="return confirm('Hapus peserta ini dari daftar hadir?')">
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($bisaUbah): ?>
            <form action="/undangan/<?= $id ?>/hadir/store" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end border-t border-gray-200 pt-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required maxlength="100" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" maxlength="100" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="<?= $inputCls ?>">
                        <?php foreach (\App\Models\DaftarHadirModel::STATUS_LABELS as $value => $label): ?>
                            <option value="<?= $value ?>"><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" maxlength="255" class="<?= $inputCls ?>">
                </div>
                <div class="md:col-span-5">
                    <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                        <i class="fas fa-plus mr-2"></i>Tambah Peserta
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Berita acara -->
    <div id="berita-acara" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-gray-800"><i class="fas fa-file-signature mr-2 text-[#1e3a5f]"></i>Berita Acara</h4>
            <?php if ($beritaAcara): ?>
                <a href="/undangan/<?= $id ?>/berita-acara/cetak" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">
                    <i class="fas fa-print mr-1"></i>Cetak
                </a>
            <?php endif; ?>
        </div>

        <?php if ($bisaUbah): ?>
            <form action="/undangan/<?= $id ?>/berita-acara/save" method="POST" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor</label>
                    <input type="text" name="nomor" maxlength="100" value="<?= esc($beritaAcara['nomor'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Uraian Jalannya Rapat <span class="text-red-500">*</span></label>
                    <textarea name="uraian" rows="5" required class="<?= $inputCls ?>"><?= esc($beritaAcara['uraian'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keputusan / Kesimpulan</label>
                    <textarea name="keputusan" rows="3" class="<?= $inputCls ?>"><?= esc($beritaAcara['keputusan'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan Berita Acara
                </button>
            </form>
        <?php elseif ($beritaAcara): ?>
            <div class="space-y-3 text-sm">
                <?php if (!empty($beritaAcara['nomor'])): ?><p class="text-gray-500">Nomor: <span class="text-gray-800"><?= esc($beritaAcara['nomor']) ?></span></p><?php endif; ?>
                <div class="bg-gray-50 rounded-lg p-4"><?= nl2br(esc($beritaAcara['uraian'])) ?></div>
                <?php if (!empty($beritaAcara['keputusan'])): ?>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <p class="text-green-700 font-medium mb-1">Keputusan</p>
                        <?= nl2br(esc($beritaAcara['keputusan'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-500">Berita acara belum dibuat.</p>
        <?php endif; ?>
    </div>

    <!-- Dokumen -->
    <div id="dokumen" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h4 class="font-semibold text-gray-800 mb-4"><i class="fas fa-paperclip mr-2 text-[#1e3a5f]"></i>Dokumen Rapat
            <span class="ml-1 bg-gray-200 text-gray-600 text-xs rounded-full px-2 py-0.5"><?= count($dokumen) ?></span>
        </h4>

        <?php if (empty($dokumen)): ?>
            <p class="text-sm text-gray-500 mb-4">Belum ada dokumen.</p>
        <?php else: ?>
            <ul class="divide-y divide-gray-200 mb-4">
                <?php foreach ($dokumen as $d): ?>
                    <li class="py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="/dokumen/<?= $d['id'] ?>/download" class="font-medium text-blue-600 hover:text-blue-800 truncate block">
                                <i class="fas fa-download mr-2"></i><?= esc($d['judul']) ?>
                            </a>
                            <p class="text-xs text-gray-500">
                                <?= esc($d['nama_asli']) ?> &middot; <?= number_format($d['ukuran'] / 1024, 0, ',', '.') ?> KB &middot;
                                <?= esc($d['uploaded_by_nama']) ?>, <?= date('d/m/Y H:i', strtotime($d['created_at'])) ?>
                            </p>
                        </div>
                        <?php if ($bisaUbah): ?>
                            <form action="/dokumen/<?= $d['id'] ?>/delete" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($bisaUbah): ?>
            <form action="/undangan/<?= $id ?>/dokumen/store" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end border-t border-gray-200 pt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                    <input type="text" name="judul" maxlength="150" placeholder="Kosong = nama berkas" class="<?= $inputCls ?>">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berkas <span class="text-red-500">*</span></label>
                    <input type="file" name="berkas" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" class="<?= $inputCls ?>">
                    <p class="text-xs text-gray-500 mt-1">PDF, Word, Excel, PowerPoint, JPG, atau PNG. Maksimal 10 MB.</p>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                        <i class="fas fa-upload mr-2"></i>Unggah Dokumen
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
