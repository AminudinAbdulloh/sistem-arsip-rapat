<?php $isEdit = $undangan !== null; ?>
<div class="page-header">
  <div class="breadcrumb">
    <i class="fas fa-home"></i>
    <a href="<?= BASE_URL ?>/index.php?url=dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <a href="<?= BASE_URL ?>/index.php?url=undangan">Undangan Rapat</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <?= $isEdit ? 'Edit' : 'Tambah' ?>
  </div>
  <h1>
    <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>" style="color:var(--primary-light)"></i>
    <?= $isEdit ? 'Edit Undangan Rapat' : 'Tambah Undangan Rapat' ?>
  </h1>
</div>

<div class="card">
  <div class="card-header">
    <h2><?= $isEdit ? 'Form Edit Undangan' : 'Form Tambah Undangan' ?></h2>
    <a href="<?= BASE_URL ?>/index.php?url=undangan" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
  <div class="card-body">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Hari <span class="req">*</span></label>
          <select name="hari" class="form-control" required>
            <option value="">-- Pilih Hari --</option>
            <?php foreach ($hariList as $h): ?>
              <option value="<?= $h ?>" <?= ($undangan['hari'] ?? '') === $h ? 'selected' : '' ?>><?= $h ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Waktu Rapat <span class="req">*</span></label>
          <input type="datetime-local" name="waktu" class="form-control" required
            value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($undangan['waktu'])) : '' ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Tempat <span class="req">*</span></label>
        <input type="text" name="tempat" class="form-control" placeholder="Contoh: Ruang Rapat Prodi Lt. 2" required
          value="<?= htmlspecialchars($undangan['tempat'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Acara / Agenda <span class="req">*</span></label>
        <textarea name="acara" class="form-control" placeholder="Tuliskan agenda rapat..." required rows="5"><?= htmlspecialchars($undangan['acara'] ?? '') ?></textarea>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
        </button>
        <a href="<?= BASE_URL ?>/index.php?url=undangan" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </div>
    </form>
  </div>
</div>