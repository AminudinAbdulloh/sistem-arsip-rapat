<div class="page-header">
    <div>
        <h1><i class="fas fa-edit" style="color:var(--accent)"></i> Edit Notulensi Rapat</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="/notulensi">Notulensi Rapat</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <span>Edit</span>
        </div>
    </div>
</div>

<div class="card" style="max-width:800px">
    <div class="card-header">
        <h2><i class="fas fa-clipboard-list"></i> Form Edit Notulensi Rapat</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="/notulensi/<?= $notulensi['id'] ?>/update" enctype="multipart/form-data">
            <div class="form-group">
                <label for="undangan_id">Undangan Rapat Terkait <span class="required">*</span></label>
                <select id="undangan_id" name="undangan_id" class="form-control" required>
                    <option value="">-- Pilih Undangan Rapat --</option>
                    <?php foreach ($undangan as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $notulensi['undangan_id'] == $u['id'] ? 'selected' : '' ?>>
                            [<?= date('d/m/Y', strtotime($u['waktu'])) ?>] <?= htmlspecialchars($u['acara']) ?> - <?= htmlspecialchars($u['tempat']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tgl_rapat">Tanggal Rapat <span class="required">*</span></label>
                    <input type="date" id="tgl_rapat" name="tgl_rapat" class="form-control" required
                           value="<?= $notulensi['tgl_rapat'] ?>">
                </div>
                <div class="form-group">
                    <label for="tema_rapat">Tema Rapat <span class="required">*</span></label>
                    <input type="text" id="tema_rapat" name="tema_rapat" class="form-control"
                           value="<?= htmlspecialchars($notulensi['tema_rapat']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="deskripsi_rapat">Deskripsi Rapat <span class="required">*</span></label>
                <textarea id="deskripsi_rapat" name="deskripsi_rapat" class="form-control" rows="5" required><?= htmlspecialchars($notulensi['deskripsi_rapat']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="catatan">Catatan Tambahan</label>
                <textarea id="catatan" name="catatan" class="form-control" rows="3"><?= htmlspecialchars($notulensi['catatan']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Foto Dokumentasi</label>
                <?php if ($notulensi['dokumentasi']): ?>
                <div style="margin-bottom:12px;padding:12px;background:#f7fafc;border-radius:8px;display:inline-flex;align-items:center;gap:12px">
                    <img src="/uploads/dokumentasi/<?= htmlspecialchars($notulensi['dokumentasi']) ?>" alt="Dokumentasi" style="width:80px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                    <div>
                        <div style="font-size:13px;font-weight:600">Foto saat ini</div>
                        <div style="font-size:12px;color:var(--text-muted)">Upload baru untuk mengganti</div>
                    </div>
                </div>
                <?php endif; ?>
                <input type="file" id="dokumentasi" name="dokumentasi" accept="image/*" class="form-control" onchange="previewImage(this)">
                <div id="imagePreview" style="display:none;margin-top:12px">
                    <img id="previewImg" src="" alt="Preview" style="max-width:200px;border-radius:8px;border:2px solid var(--border)">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="/notulensi" class="btn btn-outline">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
