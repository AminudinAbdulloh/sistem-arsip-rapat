<?php
$isEdit  = isset($notulensi) && $notulensi !== null;
$baseUrl = BASE_URL;
?>
<div class="page-header">
  <div class="breadcrumb">
    <i class="fas fa-home"></i>
    <a href="<?= $baseUrl ?>/index.php?url=dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <a href="<?= $baseUrl ?>/index.php?url=notulensi">Notulensi Rapat</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <?= $isEdit ? 'Edit' : 'Tambah' ?>
  </div>
  <h1>
    <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>" style="color:var(--primary-light)"></i>
    <?= $isEdit ? 'Edit Notulensi Rapat' : 'Tambah Notulensi Rapat' ?>
  </h1>
</div>

<style>
  /* Upload zone */
  .upload-zone {
    border: 2px dashed var(--border); border-radius: 10px;
    padding: 28px 20px; text-align: center; cursor: pointer;
    transition: border-color .2s, background .2s; background: #fafbfc; position: relative;
  }
  .upload-zone:hover, .upload-zone.drag-over { border-color: var(--primary-light); background: #eff6ff; }
  .upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
  .uz-icon  { font-size: 32px; color: #93c5fd; margin-bottom: 8px; }
  .uz-label { font-size: 14px; color: var(--muted); }
  .uz-label strong { color: var(--primary-light); }
  .uz-hint  { font-size: 11px; color: #9ca3af; margin-top: 4px; }

  /* Foto grid */
  .foto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; margin-top: 12px; }
  .foto-item { position: relative; border-radius: 8px; overflow: hidden; border: 2px solid var(--border); background: #f1f5f9; aspect-ratio: 1; }
  .foto-item img  { width: 100%; height: 100%; object-fit: cover; display: block; }
  .foto-item .foto-del {
    position: absolute; top: 4px; right: 4px;
    background: rgba(239,68,68,.85); color: white; border: none; border-radius: 50%;
    width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 11px; transition: background .2s;
  }
  .foto-item .foto-del:hover { background: #dc2626; }
  .foto-item .foto-badge { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.45); color: white; font-size: 10px; padding: 3px 6px; text-align: center; }
  .foto-new-badge { position: absolute; top: 4px; left: 4px; background: var(--success); color: white; font-size: 9px; padding: 2px 6px; border-radius: 10px; }

  /* Dokumen list (shared) */
  .dok-detail-list { margin-top: 10px; display: flex; flex-direction: column; gap: 8px; }
  .dok-detail-item {
    display: flex; align-items: center; gap: 14px;
    background: #f8fafc; border: 1px solid var(--border);
    border-radius: 10px; padding: 12px 16px; transition: border-color .2s;
  }
  .dok-detail-item:hover { border-color: var(--primary-light); background: #eff6ff; }
  .dok-icon-big { font-size: 26px; flex-shrink: 0; }
  .dok-info  { flex: 1; }
  .dok-filename { font-size: 14px; font-weight: 600; color: var(--primary); text-decoration: none; display: block; margin-bottom: 3px; }
  .dok-filename:hover { color: var(--primary-light); }
  .dok-meta  { font-size: 11px; color: var(--muted); }
  .btn-dl {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; background: var(--primary-light); color: white;
    border-radius: 7px; font-size: 12px; font-weight: 600;
    text-decoration: none; white-space: nowrap; transition: background .2s;
  }
  .btn-dl:hover { background: #1d4ed8; }

  /* Preview dokumen baru */
  .dok-new-item {
    display: flex; align-items: center; gap: 10px;
    background: #f8fafc; border: 1px solid var(--border);
    border-radius: 8px; padding: 10px 14px;
  }
  .dok-new-badge { font-size: 9px; background: var(--success); color: white; padding: 2px 6px; border-radius: 10px; white-space: nowrap; }
  .dok-del-btn   { background: none; border: 1px solid #fca5a5; color: var(--danger); border-radius: 6px; padding: 4px 8px; font-size: 11px; cursor: pointer; transition: background .2s; }
  .dok-del-btn:hover { background: #fef2f2; }
</style>

<div class="card">
  <div class="card-header">
    <h2><?= $isEdit ? 'Form Edit Notulensi' : 'Form Tambah Notulensi' ?></h2>
    <a href="<?= $baseUrl ?>/index.php?url=notulensi" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </div>
  <div class="card-body">

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="mainForm">

      <!-- Undangan -->
      <div class="form-group">
        <label class="form-label">Undangan Rapat <span class="req">*</span></label>
        <select name="undangan_id" class="form-control" required id="undanganSelect">
          <option value="">-- Pilih Undangan Rapat --</option>
          <?php foreach ($undanganList as $u): ?>
            <option value="<?= $u['id'] ?>"
              <?= ($notulensi['undangan_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>
              data-waktu="<?= date('d/m/Y H:i', strtotime($u['waktu'])) ?>"
              data-tempat="<?= htmlspecialchars($u['tempat']) ?>"
              data-acara="<?= htmlspecialchars(substr($u['acara'], 0, 80)) ?>">
              <?= htmlspecialchars(date('d/m/Y H:i', strtotime($u['waktu'])) . ' - ' . substr($u['acara'], 0, 60)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Info undangan yang dipilih -->
      <div id="undanganInfo" style="display:none;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;margin-bottom:20px">
        <p style="font-size:13px;color:#1e40af;margin-bottom:4px;font-weight:600"><i class="fas fa-info-circle"></i> Info Undangan yang Dipilih</p>
        <p style="font-size:13px;margin-bottom:4px"><i class="fas fa-calendar" style="color:var(--primary-light);width:16px"></i> <span id="infoWaktu"></span></p>
        <p style="font-size:13px;margin-bottom:4px"><i class="fas fa-map-marker-alt" style="color:var(--danger);width:16px"></i> <span id="infoTempat"></span></p>
        <p style="font-size:13px;margin-bottom:0"><i class="fas fa-clipboard" style="color:var(--warning);width:16px"></i> <span id="infoAcara"></span></p>
      </div>

      <!-- Deskripsi -->
      <div class="form-group">
        <label class="form-label">Deskripsi Rapat <span class="req">*</span></label>
        <textarea name="deskripsi_rapat" class="form-control" rows="6"
          placeholder="Tuliskan hasil dan jalannya rapat secara rinci..." required><?= htmlspecialchars($notulensi['deskripsi_rapat'] ?? '') ?></textarea>
      </div>

      <!-- Catatan -->
      <div class="form-group">
        <label class="form-label">Catatan Tambahan</label>
        <textarea name="catatan" class="form-control" rows="3"
          placeholder="Catatan penting, tindak lanjut, dsb..."><?= htmlspecialchars($notulensi['catatan'] ?? '') ?></textarea>
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">

      <!-- ============= FOTO ============= -->
      <div class="form-group">
        <label class="form-label" style="font-size:15px">
          <i class="fas fa-images" style="color:var(--primary-light)"></i>
          Dokumentasi Foto <span style="font-weight:400;color:var(--muted);font-size:12px">— bisa lebih dari satu</span>
        </label>

        <?php if ($isEdit && !empty($notulensi['dokumentasi_list'])): ?>
        <p style="font-size:12px;color:var(--muted);margin-bottom:8px">Foto tersimpan — centang untuk menghapus.</p>
        <div class="foto-grid">
          <?php foreach ($notulensi['dokumentasi_list'] as $foto): ?>
          <div class="foto-item" id="foto-wrap-<?= $foto['id'] ?>">
            <img src="<?= $baseUrl ?>/public/uploads/dokumentasi/<?= htmlspecialchars($foto['filename']) ?>" alt="" loading="lazy">
            <label style="position:absolute;top:4px;left:4px;cursor:pointer">
              <input type="checkbox" name="hapus_dokumentasi[]" value="<?= $foto['id'] ?>"
                     onchange="toggleHapusFoto(this, <?= $foto['id'] ?>)" style="display:none">
              <span id="badge-foto-<?= $foto['id'] ?>"
                    style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;
                           background:rgba(0,0,0,.4);border-radius:50%;color:white;font-size:10px">
                <i class="fas fa-check" style="opacity:0.3"></i>
              </span>
            </label>
            <div class="foto-badge"><?= htmlspecialchars(substr($foto['filename'], 0, 20)) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="upload-zone" id="fotoZone" style="margin-top:<?= $isEdit && !empty($notulensi['dokumentasi_list']) ? '12px' : '0' ?>">
          <input type="file" name="dokumentasi[]" id="fotoInput" accept="image/*" multiple>
          <div class="uz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
          <div class="uz-label"><strong>Klik atau seret</strong> foto ke sini</div>
          <div class="uz-hint">JPG, PNG, GIF, WEBP &middot; Maks 10MB per file</div>
        </div>
        <div class="foto-grid" id="newFotoGrid"></div>
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">

      <!-- ============= DOKUMEN ============= -->
      <div class="form-group">
        <label class="form-label" style="font-size:15px">
          <i class="fas fa-paperclip" style="color:var(--warning)"></i>
          Dokumen Pendukung <span style="font-weight:400;color:var(--muted);font-size:12px">— bisa lebih dari satu</span>
        </label>

        <?php if ($isEdit && !empty($notulensi['dokumen_list'])): ?>
        <p style="font-size:12px;color:var(--muted);margin-bottom:8px">Dokumen tersimpan — centang untuk menghapus.</p>
        <div class="dok-detail-list">
          <?php foreach ($notulensi['dokumen_list'] as $dok):
            $showDelete = true;
            include __DIR__ . '/_dokumen_item.php';
          endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="upload-zone" id="dokZone" style="margin-top:<?= $isEdit && !empty($notulensi['dokumen_list']) ? '12px' : '0' ?>">
          <input type="file" name="dokumen_pendukung[]" id="dokInput"
                 accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" multiple>
          <div class="uz-icon"><i class="fas fa-folder-plus" style="color:#fbbf24"></i></div>
          <div class="uz-label"><strong>Klik atau seret</strong> dokumen ke sini</div>
          <div class="uz-hint">PDF, Word, Excel, PowerPoint &middot; Maks 10MB per file</div>
        </div>
        <div class="dok-detail-list" id="newDokList"></div>
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">

      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
        </button>
        <a href="<?= $baseUrl ?>/index.php?url=notulensi" class="btn btn-secondary">
          <i class="fas fa-times"></i> Batal
        </a>
      </div>
    </form>
  </div>
</div>

<script>
// ---- Info undangan dropdown ----
(function () {
  var select = document.getElementById('undanganSelect');
  function update() {
    var opt  = select.options[select.selectedIndex];
    var info = document.getElementById('undanganInfo');
    if (opt && opt.value) {
      document.getElementById('infoWaktu').textContent  = opt.dataset.waktu;
      document.getElementById('infoTempat').textContent = opt.dataset.tempat;
      document.getElementById('infoAcara').textContent  = opt.dataset.acara;
      info.style.display = 'block';
    } else {
      info.style.display = 'none';
    }
  }
  select.addEventListener('change', update);
  update();
})();

// ---- Toggle hapus foto existing ----
function toggleHapusFoto(cb, id) {
  var wrap  = document.getElementById('foto-wrap-' + id);
  var badge = document.getElementById('badge-foto-' + id);
  if (cb.checked) {
    wrap.style.opacity      = '0.4';
    wrap.style.borderColor  = 'var(--danger)';
    badge.innerHTML         = '<i class="fas fa-times" style="color:#ef4444"></i>';
    badge.style.background  = 'rgba(239,68,68,.7)';
  } else {
    wrap.style.opacity      = '1';
    wrap.style.borderColor  = 'var(--border)';
    badge.innerHTML         = '<i class="fas fa-check" style="opacity:0.3"></i>';
    badge.style.background  = 'rgba(0,0,0,.4)';
  }
}

// ---- Toggle hapus dokumen existing ----
function toggleHapusDok(cb, id) {
  var wrap = document.getElementById('dok-wrap-' + id);
  if (cb.checked) {
    wrap.style.opacity    = '0.45';
    wrap.style.borderColor = 'var(--danger)';
    wrap.style.background  = '#fef2f2';
  } else {
    wrap.style.opacity    = '1';
    wrap.style.borderColor = 'var(--border)';
    wrap.style.background  = '';
  }
}

// ---- Drag-over effect ----
['fotoZone', 'dokZone'].forEach(function (zoneId) {
  var zone = document.getElementById(zoneId);
  if (!zone) return;
  zone.addEventListener('dragover',  function (e) { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', function ()  { zone.classList.remove('drag-over'); });
  zone.addEventListener('drop',      function ()  { zone.classList.remove('drag-over'); });
});

// ---- Akumulasi file di array JS ----
var newFotos = [];
var newDoks  = [];

document.getElementById('fotoInput').addEventListener('change', function () {
  Array.from(this.files).forEach(function (f) {
    if (!newFotos.some(function (x) { return x.name === f.name && x.size === f.size; })) newFotos.push(f);
  });
  this.value = '';
  renderNewFotos();
});

document.getElementById('dokInput').addEventListener('change', function () {
  Array.from(this.files).forEach(function (f) {
    if (!newDoks.some(function (x) { return x.name === f.name && x.size === f.size; })) newDoks.push(f);
  });
  this.value = '';
  renderNewDoks();
});

function renderNewFotos() {
  var grid = document.getElementById('newFotoGrid');
  grid.innerHTML = '';
  newFotos.forEach(function (file, idx) {
    var reader = new FileReader();
    reader.onload = function (e) {
      var div = document.createElement('div');
      div.className = 'foto-item';
      div.innerHTML =
        '<img src="' + e.target.result + '" alt="">' +
        '<span class="foto-new-badge">Baru</span>' +
        '<button type="button" class="foto-del" onclick="removeNewFoto(' + idx + ')"><i class="fas fa-times"></i></button>' +
        '<div class="foto-badge">' + esc(file.name.substring(0, 18)) + '</div>';
      grid.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}

function removeNewFoto(idx) { newFotos.splice(idx, 1); renderNewFotos(); }

function renderNewDoks() {
  var list = document.getElementById('newDokList');
  list.innerHTML = '';
  newDoks.forEach(function (file, idx) {
    var div = document.createElement('div');
    div.className = 'dok-new-item';
    div.innerHTML =
      '<i class="fas ' + getDokIcon(file.type) + '" style="color:' + getDokColor(file.type) + ';font-size:20px;flex-shrink:0"></i>' +
      '<span style="flex:1;font-size:13px;word-break:break-all">' + esc(file.name) + '</span>' +
      '<span style="font-size:11px;color:var(--muted);white-space:nowrap">' + fmtSize(file.size) + '</span>' +
      '<span class="dok-new-badge">Baru</span>' +
      '<button type="button" class="dok-del-btn" onclick="removeNewDok(' + idx + ')"><i class="fas fa-times"></i> Hapus</button>';
    list.appendChild(div);
  });
}

function removeNewDok(idx) { newDoks.splice(idx, 1); renderNewDoks(); }

// ---- Submit via fetch ----
document.getElementById('mainForm').addEventListener('submit', function (e) {
  e.preventDefault();
  var fd = new FormData(this);
  fd.delete('dokumentasi[]');
  fd.delete('dokumen_pendukung[]');
  newFotos.forEach(function (f) { fd.append('dokumentasi[]', f, f.name); });
  newDoks.forEach(function (f)  { fd.append('dokumen_pendukung[]', f, f.name); });

  var btn = this.querySelector('button[type=submit]');
  var orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

  fetch(this.action || window.location.href, { method: 'POST', body: fd })
    .then(function (res) {
      if (res.redirected) { window.location.href = res.url; }
      else { window.location.reload(); }
    })
    .catch(function () {
      btn.disabled  = false;
      btn.innerHTML = orig;
      alert('Gagal mengirim data. Coba lagi.');
    });
});

// ---- Utils ----
function getDokIcon(mime) {
  if (mime.includes('pdf'))          return 'fa-file-pdf';
  if (mime.includes('word'))         return 'fa-file-word';
  if (mime.includes('excel') || mime.includes('spreadsheet')) return 'fa-file-excel';
  if (mime.includes('presentation') || mime.includes('powerpoint')) return 'fa-file-powerpoint';
  return 'fa-file-alt';
}
function getDokColor(mime) {
  if (mime.includes('pdf'))          return '#ef4444';
  if (mime.includes('word'))         return '#2563eb';
  if (mime.includes('excel') || mime.includes('spreadsheet')) return '#10b981';
  if (mime.includes('presentation') || mime.includes('powerpoint')) return '#f59e0b';
  return '#6b7280';
}
function fmtSize(b) {
  if (b < 1024) return b + ' B';
  if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
  return (b / 1048576).toFixed(1) + ' MB';
}
function esc(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>