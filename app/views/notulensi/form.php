<?php $isEdit = $notulensi !== null; ?>
<div class="page-header">
  <div class="breadcrumb">
    <i class="fas fa-home"></i>
    <a href="<?= BASE_URL ?>/index.php?url=dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <a href="<?= BASE_URL ?>/index.php?url=notulensi">Notulensi Rapat</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <?= $isEdit ? 'Edit' : 'Tambah' ?>
  </div>
  <h1>
    <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>" style="color:var(--primary-light)"></i>
    <?= $isEdit ? 'Edit Notulensi Rapat' : 'Tambah Notulensi Rapat' ?>
  </h1>
</div>

<div class="card">
  <div class="card-header">
    <h2><?= $isEdit ? 'Form Edit Notulensi' : 'Form Tambah Notulensi' ?></h2>
    <a href="<?= BASE_URL ?>/index.php?url=notulensi" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
  <div class="card-body">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label class="form-label">Undangan Rapat <span class="req">*</span></label>
        <select name="undangan_id" class="form-control" required>
          <option value="">-- Pilih Undangan Rapat --</option>
          <?php foreach ($undanganList as $u): ?>
            <option value="<?= $u['id'] ?>" <?= ($notulensi['undangan_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['hari'] . ', ' . date('d/m/Y H:i', strtotime($u['waktu'])) . ' - ' . substr($u['acara'], 0, 60)) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="form-hint">Pilih undangan rapat yang sudah dibuat sebelumnya.</p>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Tanggal Rapat <span class="req">*</span></label>
          <input type="date" name="tgl_rapat" class="form-control" required
            value="<?= htmlspecialchars($notulensi['tgl_rapat'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Tema Rapat <span class="req">*</span></label>
          <input type="text" name="tema_rapat" class="form-control" placeholder="Tema utama rapat" required
            value="<?= htmlspecialchars($notulensi['tema_rapat'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Deskripsi Rapat <span class="req">*</span></label>
        <textarea name="deskripsi_rapat" class="form-control" rows="6" placeholder="Tuliskan hasil dan jalannya rapat secara rinci..." required><?= htmlspecialchars($notulensi['deskripsi_rapat'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Catatan Tambahan</label>
        <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan penting, tindak lanjut, dsb..."><?= htmlspecialchars($notulensi['catatan'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Dokumentasi (Foto)</label>
        <?php if ($isEdit && $notulensi['dokumentasi']): ?>
          <div style="margin-bottom:10px">
            <img src="<?= BASE_URL ?>/public/uploads/dokumentasi/<?= htmlspecialchars($notulensi['dokumentasi']) ?>"
                 style="max-height:120px;border-radius:8px;border:2px solid var(--border)" alt="Dokumentasi">
            <p class="form-hint">Upload baru untuk mengganti foto di atas.</p>
          </div>
        <?php endif; ?>
        <input type="file" name="dokumentasi" class="form-control" accept="image/*" id="fotoInput">
        <p class="form-hint">Format: JPG, PNG, GIF, WEBP. Maks 5MB.</p>
        <div id="previewWrap" style="margin-top:10px;display:none">
          <img id="previewImg" style="max-height:150px;border-radius:8px;border:2px solid var(--primary-light)" alt="Preview">
        </div>
      </div>

      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
        </button>
        <a href="<?= BASE_URL ?>/index.php?url=notulensi" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('fotoInput').addEventListener('change', function() {
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('previewImg').src = e.target.result;
      document.getElementById('previewWrap').style.display = 'block';
    };
    reader.readAsDataURL(this.files[0]);
  }
});
</script>