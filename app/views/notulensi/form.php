<?php $isEdit = isset($notulensi) && $notulensi !== null; ?>
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
        <select name="undangan_id" class="form-control" required id="undanganSelect">
          <option value="">-- Pilih Undangan Rapat --</option>
          <?php foreach ($undanganList as $u): 
            $sudahPunyaNotulensi = $isEdit 
              ? ($u['id'] != $notulensi['undangan_id'] && isset($u['has_notulensi']) && $u['has_notulensi'])
              : false;
          ?>
            <option value="<?= $u['id'] ?>" 
              <?= ($notulensi['undangan_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>
              <?= $sudahPunyaNotulensi ? 'disabled' : '' ?>
              data-waktu="<?= date('d/m/Y H:i', strtotime($u['waktu'])) ?>"
              data-tempat="<?= htmlspecialchars($u['tempat']) ?>"
              data-acara="<?= htmlspecialchars(substr($u['acara'], 0, 80)) ?>">
              <?= htmlspecialchars(date('d/m/Y H:i', strtotime($u['waktu'])) . ' - ' . substr($u['acara'], 0, 60)) ?>
              <?= $sudahPunyaNotulensi ? ' (sudah ada notulensi)' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="form-hint">Pilih undangan rapat. Tanggal dan tema akan otomatis diambil dari undangan yang dipilih.</p>
      </div>

      <!-- Info undangan yang dipilih (read-only) -->
      <div id="undanganInfo" style="display:none;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;margin-bottom:20px">
        <p style="font-size:13px;color:#1e40af;margin-bottom:4px;font-weight:600"><i class="fas fa-info-circle"></i> Info Undangan yang Dipilih</p>
        <p style="font-size:13px;margin-bottom:4px"><i class="fas fa-calendar" style="color:var(--primary-light);width:16px"></i> <span id="infoWaktu"></span></p>
        <p style="font-size:13px;margin-bottom:4px"><i class="fas fa-map-marker-alt" style="color:var(--danger);width:16px"></i> <span id="infoTempat"></span></p>
        <p style="font-size:13px;margin-bottom:0"><i class="fas fa-clipboard" style="color:var(--warning);width:16px"></i> <span id="infoAcara"></span></p>
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
// Preview foto
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

// Tampilkan info undangan saat pilih
var select = document.getElementById('undanganSelect');
function updateUndanganInfo() {
  var opt = select.options[select.selectedIndex];
  var info = document.getElementById('undanganInfo');
  if (opt && opt.value) {
    document.getElementById('infoWaktu').textContent = opt.getAttribute('data-waktu');
    document.getElementById('infoTempat').textContent = opt.getAttribute('data-tempat');
    document.getElementById('infoAcara').textContent = opt.getAttribute('data-acara');
    info.style.display = 'block';
  } else {
    info.style.display = 'none';
  }
}
select.addEventListener('change', updateUndanganInfo);
// Inisialisasi jika sudah ada pilihan (mode edit)
updateUndanganInfo();
</script>