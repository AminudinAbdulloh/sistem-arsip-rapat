<?php
$formatterLengkap = new IntlDateFormatter(
  'id_ID',
  IntlDateFormatter::FULL,
  IntlDateFormatter::NONE
);
?>

<div class="page-header">
  <div class="breadcrumb">
    <i class="fas fa-home"></i>
    <a href="<?= BASE_URL ?>/index.php?url=dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <a href="<?= BASE_URL ?>/index.php?url=notulensi">Notulensi Rapat</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    Detail
  </div>
  <h1><i class="fas fa-file-alt" style="color:var(--primary-light)"></i> Detail Notulensi Rapat</h1>
</div>

<style>
  /* ---- Galeri Foto ---- */
  .foto-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
    margin-top: 10px;
  }
  .foto-gallery-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid var(--border);
    background: #f1f5f9;
    aspect-ratio: 1;
    cursor: pointer;
    transition: border-color .2s, transform .15s;
  }
  .foto-gallery-item:hover {
    border-color: var(--primary-light);
    transform: scale(1.02);
  }
  .foto-gallery-item img {
    width: 100%; height: 100%; object-fit: cover; display: block;
  }
  .foto-gallery-item .foto-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0); display: flex;
    align-items: center; justify-content: center;
    transition: background .2s; color: white; font-size: 22px; opacity: 0;
  }
  .foto-gallery-item:hover .foto-overlay {
    background: rgba(0,0,0,.3); opacity: 1;
  }
  .foto-gallery-item .foto-num {
    position: absolute; bottom: 6px; right: 8px;
    background: rgba(0,0,0,.5); color: white;
    font-size: 10px; padding: 2px 6px; border-radius: 10px;
  }

  /* ---- Dokumen List ---- */
  .dok-detail-list { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
  .dok-detail-item {
    display: flex; align-items: center; gap: 14px;
    background: #f8fafc; border: 1px solid var(--border);
    border-radius: 10px; padding: 12px 16px;
    transition: border-color .2s;
  }
  .dok-detail-item:hover { border-color: var(--primary-light); background: #eff6ff; }
  .dok-detail-item .dok-icon-big { font-size: 26px; flex-shrink: 0; }
  .dok-detail-item .dok-info { flex: 1; }
  .dok-detail-item .dok-info .dok-filename {
    font-size: 14px; font-weight: 600; color: var(--primary);
    text-decoration: none; display: block; margin-bottom: 3px;
  }
  .dok-detail-item .dok-info .dok-filename:hover { color: var(--primary-light); }
  .dok-detail-item .dok-info .dok-meta { font-size: 11px; color: var(--muted); }
  .dok-detail-item .btn-dl {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; background: var(--primary-light); color: white;
    border-radius: 7px; font-size: 12px; font-weight: 600;
    text-decoration: none; white-space: nowrap; transition: background .2s;
  }
  .dok-detail-item .btn-dl:hover { background: #1d4ed8; }

  /* ---- Lightbox ---- */
  .lightbox {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.85); z-index: 9000;
    align-items: center; justify-content: center;
  }
  .lightbox.show { display: flex; }
  .lightbox img {
    max-width: 92vw; max-height: 88vh;
    border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,.6);
  }
  .lightbox-close {
    position: fixed; top: 18px; right: 22px;
    color: white; font-size: 30px; cursor: pointer;
    background: none; border: none; line-height: 1;
  }
  .lightbox-nav {
    position: fixed; top: 50%; transform: translateY(-50%);
    color: white; font-size: 28px; cursor: pointer;
    background: rgba(255,255,255,.15); border: none; border-radius: 50%;
    width: 46px; height: 46px; display: flex; align-items: center; justify-content: center;
    transition: background .2s;
  }
  .lightbox-nav:hover { background: rgba(255,255,255,.3); }
  .lightbox-nav.prev { left: 16px; }
  .lightbox-nav.next { right: 16px; }
  .lightbox-counter {
    position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
    color: rgba(255,255,255,.8); font-size: 13px;
    background: rgba(0,0,0,.4); padding: 4px 14px; border-radius: 20px;
  }

  @media (max-width:768px) {
    .detail-grid { grid-template-columns: 1fr !important; }
    .foto-gallery { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
  }
</style>

<div class="card">
  <div class="card-header">
    <h2><?= htmlspecialchars($notulensi['tema_rapat']) ?></h2>
    <div style="display:flex;gap:8px">
      <a href="<?= BASE_URL ?>/index.php?url=notulensi/edit/<?= $notulensi['id'] ?>" class="btn btn-warning btn-sm">
        <i class="fas fa-edit"></i> Edit
      </a>
      <a href="<?= BASE_URL ?>/index.php?url=notulensi" class="btn btn-outline btn-sm">
        <i class="fas fa-arrow-left"></i> Kembali
      </a>
    </div>
  </div>
  <div class="card-body">

    <!-- Info Rapat -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="detail-grid">
      <div>
        <h3 style="font-size:13px;color:var(--muted);margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px">Informasi Rapat</h3>
        <table style="width:100%;font-size:14px">
          <tr>
            <td style="padding:8px 0;color:var(--muted);width:150px;vertical-align:top">
              <i class="fas fa-calendar" style="color:var(--primary-light)"></i> <strong>Hari/Tanggal</strong>
            </td>
            <td style="padding:8px 0">
              <strong>
                <?php
                  $dt = new DateTime($notulensi['tgl_rapat']);
                  $formatterLengkap->setPattern("EEEE / d MMMM y");
                  echo $formatterLengkap->format($dt);
                ?>
              </strong>
            </td>
          </tr>
          <tr>
            <td style="padding:8px 0;color:var(--muted);vertical-align:top">
              <i class="fas fa-clipboard" style="color:var(--warning)"></i> <strong>Tema Rapat</strong>
            </td>
            <td style="padding:8px 0"><?= htmlspecialchars($notulensi['tema_rapat']) ?></td>
          </tr>
          <tr>
            <td style="padding:8px 0;color:var(--muted);vertical-align:top">
              <i class="fas fa-map-marker-alt" style="color:var(--danger)"></i> <strong>Tempat</strong>
            </td>
            <td style="padding:8px 0"><?= htmlspecialchars($notulensi['tempat']) ?></td>
          </tr>
        </table>
      </div>

      <!-- Thumbnail galeri (3 teratas) -->
      <div>
        <?php if (!empty($notulensi['dokumentasi_list'])): ?>
        <h3 style="font-size:13px;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">
          Dokumentasi Foto (<?= count($notulensi['dokumentasi_list']) ?>)
        </h3>
        <div class="foto-gallery">
          <?php foreach ($notulensi['dokumentasi_list'] as $fi => $foto): ?>
          <div class="foto-gallery-item" onclick="openLightbox(<?= $fi ?>)" title="Klik untuk perbesar">
            <img src="<?= BASE_URL ?>/public/uploads/dokumentasi/<?= htmlspecialchars($foto['filename']) ?>"
                 alt="Foto <?= $fi + 1 ?>" loading="lazy">
            <div class="foto-overlay"><i class="fas fa-search-plus"></i></div>
            <span class="foto-num"><?= $fi + 1 ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="background:#f8fafc;border-radius:8px;padding:36px;text-align:center;
                    color:var(--muted);border:2px dashed var(--border)">
          <i class="fas fa-image" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px"></i>
          Tidak ada dokumentasi foto
        </div>
        <?php endif; ?>
      </div>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">

    <!-- Deskripsi -->
    <div class="form-group">
      <label class="form-label" style="font-size:15px">Deskripsi Rapat</label>
      <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid var(--border);
                  font-size:14px;line-height:1.7;white-space:pre-wrap"><?= htmlspecialchars($notulensi['deskripsi_rapat']) ?></div>
    </div>

    <?php if (!empty($notulensi['catatan'])): ?>
    <div class="form-group">
      <label class="form-label" style="font-size:15px">Catatan Tambahan</label>
      <div style="background:#fffbeb;padding:16px;border-radius:8px;border:1px solid #fcd34d;
                  font-size:14px;line-height:1.7;white-space:pre-wrap"><?= htmlspecialchars($notulensi['catatan']) ?></div>
    </div>
    <?php endif; ?>

    <!-- Dokumen Pendukung -->
    <?php if (!empty($notulensi['dokumen_list'])): ?>
    <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">
    <div class="form-group">
      <label class="form-label" style="font-size:15px">
        <i class="fas fa-paperclip" style="color:var(--warning)"></i>
        Dokumen Pendukung (<?= count($notulensi['dokumen_list']) ?>)
      </label>
      <div class="dok-detail-list">
        <?php foreach ($notulensi['dokumen_list'] as $dok):
          $iconClass = str_contains($dok['mime_type'], 'pdf') ? 'fa-file-pdf' :
                       (str_contains($dok['mime_type'], 'word') ? 'fa-file-word' :
                       (str_contains($dok['mime_type'], 'excel') || str_contains($dok['mime_type'], 'spreadsheet') ? 'fa-file-excel' :
                       (str_contains($dok['mime_type'], 'presentation') || str_contains($dok['mime_type'], 'powerpoint') ? 'fa-file-powerpoint' : 'fa-file-alt')));
          $iconColor = str_contains($dok['mime_type'], 'pdf') ? '#ef4444' :
                       (str_contains($dok['mime_type'], 'word') ? '#2563eb' :
                       (str_contains($dok['mime_type'], 'excel') || str_contains($dok['mime_type'], 'spreadsheet') ? '#10b981' : '#f59e0b'));
          $uploadedAt = date('d/m/Y', strtotime($dok['created_at']));
        ?>
        <div class="dok-detail-item">
          <i class="fas <?= $iconClass ?> dok-icon-big" style="color:<?= $iconColor ?>"></i>
          <div class="dok-info">
            <a href="<?= BASE_URL ?>/public/uploads/dokumen/<?= htmlspecialchars($dok['filename']) ?>"
               target="_blank" class="dok-filename">
              <?= htmlspecialchars($dok['original_name']) ?>
            </a>
            <span class="dok-meta">Diupload: <?= $uploadedAt ?> &nbsp;·&nbsp; <?= htmlspecialchars($dok['mime_type']) ?></span>
          </div>
          <a href="<?= BASE_URL ?>/public/uploads/dokumen/<?= htmlspecialchars($dok['filename']) ?>"
             download="<?= htmlspecialchars($dok['original_name']) ?>"
             class="btn-dl">
            <i class="fas fa-download"></i> Unduh
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php else: ?>
    <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">
    <div style="background:#f8fafc;border-radius:8px;padding:20px;text-align:center;
                color:var(--muted);border:1px dashed var(--border);font-size:14px">
      <i class="fas fa-paperclip" style="opacity:.3;margin-right:6px"></i>
      Tidak ada dokumen pendukung
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- ==================== LIGHTBOX ==================== -->
<?php if (!empty($notulensi['dokumentasi_list'])): ?>
<div class="lightbox" id="lightbox" onclick="closeLightboxBg(event)">
  <button class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
  <button class="lightbox-nav prev" onclick="lightboxNav(-1)"><i class="fas fa-chevron-left"></i></button>
  <img src="" alt="Foto" id="lightboxImg">
  <button class="lightbox-nav next" onclick="lightboxNav(1)"><i class="fas fa-chevron-right"></i></button>
  <div class="lightbox-counter" id="lightboxCounter"></div>
</div>
<script>
var lbFotos = <?= json_encode(array_map(function($f) use ($notulensi) {
  return BASE_URL . '/public/uploads/dokumentasi/' . $f['filename'];
}, $notulensi['dokumentasi_list'])) ?>;
var lbCurrent = 0;

function openLightbox(idx) {
  lbCurrent = idx;
  renderLightbox();
  document.getElementById('lightbox').classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('show');
  document.body.style.overflow = '';
}
function closeLightboxBg(e) {
  if (e.target === document.getElementById('lightbox')) closeLightbox();
}
function lightboxNav(dir) {
  lbCurrent = (lbCurrent + dir + lbFotos.length) % lbFotos.length;
  renderLightbox();
}
function renderLightbox() {
  document.getElementById('lightboxImg').src = lbFotos[lbCurrent];
  document.getElementById('lightboxCounter').textContent = (lbCurrent + 1) + ' / ' + lbFotos.length;
  document.querySelector('.lightbox-nav.prev').style.display = lbFotos.length > 1 ? '' : 'none';
  document.querySelector('.lightbox-nav.next').style.display = lbFotos.length > 1 ? '' : 'none';
}
document.addEventListener('keydown', function(e) {
  if (!document.getElementById('lightbox').classList.contains('show')) return;
  if (e.key === 'ArrowLeft')  lightboxNav(-1);
  if (e.key === 'ArrowRight') lightboxNav(1);
  if (e.key === 'Escape')     closeLightbox();
});
</script>
<?php endif; ?>