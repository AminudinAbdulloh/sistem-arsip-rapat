<div class="page-header">
    <div>
        <h1><i class="fas fa-plus-circle" style="color:var(--accent)"></i> Tambah Notulensi Rapat</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="/notulensi">Notulensi Rapat</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <span>Tambah</span>
        </div>
    </div>
</div>

<div class="card" style="max-width:800px">
    <div class="card-header">
        <h2><i class="fas fa-clipboard-list"></i> Form Notulensi Rapat</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="/notulensi/store" enctype="multipart/form-data">
            <div class="form-group">
                <label for="undangan_id">Undangan Rapat Terkait <span class="required">*</span></label>
                <select id="undangan_id" name="undangan_id" class="form-control" required>
                    <option value="">-- Pilih Undangan Rapat --</option>
                    <?php foreach ($undangan as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($_POST['undangan_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            [<?= date('d/m/Y', strtotime($u['waktu'])) ?>] <?= htmlspecialchars($u['acara']) ?> - <?= htmlspecialchars($u['tempat']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block">
                    <i class="fas fa-info-circle"></i> Pilih undangan rapat yang sudah dibuat sebelumnya.
                </small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tgl_rapat">Tanggal Rapat <span class="required">*</span></label>
                    <input type="date" id="tgl_rapat" name="tgl_rapat" class="form-control" required
                           value="<?= htmlspecialchars($_POST['tgl_rapat'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="tema_rapat">Tema Rapat <span class="required">*</span></label>
                    <input type="text" id="tema_rapat" name="tema_rapat" class="form-control"
                           placeholder="Tema utama rapat"
                           value="<?= htmlspecialchars($_POST['tema_rapat'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="deskripsi_rapat">Deskripsi Rapat <span class="required">*</span></label>
                <textarea id="deskripsi_rapat" name="deskripsi_rapat" class="form-control" rows="5"
                          placeholder="Tuliskan hasil pembahasan rapat secara detail..." required><?= htmlspecialchars($_POST['deskripsi_rapat'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="catatan">Catatan Tambahan</label>
                <textarea id="catatan" name="catatan" class="form-control" rows="3"
                          placeholder="Catatan, tindak lanjut, atau hal penting lainnya..."><?= htmlspecialchars($_POST['catatan'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="dokumentasi">Foto Dokumentasi</label>
                <div style="border:2px dashed var(--border);border-radius:10px;padding:24px;text-align:center;cursor:pointer;transition:border-color 0.2s" id="dropzone">
                    <i class="fas fa-cloud-upload-alt" style="font-size:36px;color:#a0aec0;margin-bottom:8px"></i>
                    <p style="font-size:14px;color:var(--text-muted);margin-bottom:8px">Klik atau drag foto dokumentasi rapat</p>
                    <p style="font-size:12px;color:#a0aec0">Format: JPG, PNG, GIF, WEBP (Max 5MB)</p>
                    <input type="file" id="dokumentasi" name="dokumentasi" accept="image/*" style="display:none" onchange="previewImage(this)">
                    <label for="dokumentasi" class="btn btn-outline btn-sm" style="margin-top:12px;cursor:pointer">
                        <i class="fas fa-image"></i> Pilih Foto
                    </label>
                </div>
                <div id="imagePreview" style="display:none;margin-top:12px">
                    <img id="previewImg" src="" alt="Preview" style="max-width:200px;border-radius:8px;border:2px solid var(--border)">
                    <button type="button" onclick="clearImage()" class="btn btn-danger btn-sm" style="margin-left:10px;vertical-align:top">
                        <i class="fas fa-times"></i> Hapus
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Notulensi
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
function clearImage() {
    document.getElementById('dokumentasi').value = '';
    document.getElementById('imagePreview').style.display = 'none';
}
</script>
