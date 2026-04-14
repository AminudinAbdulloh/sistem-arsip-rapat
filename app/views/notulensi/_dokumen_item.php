<?php
/**
 * Partial: _dokumen_item.php
 *
 * Menampilkan satu baris dokumen pendukung.
 * Digunakan oleh notulensi/detail.php dan notulensi/form.php
 *
 * Variabel yang dibutuhkan:
 *   $dok        — array data dokumen (filename, original_name, mime_type, created_at)
 *   $showDelete — bool, tampilkan kontrol hapus (default: false)
 *   $baseUrl    — BASE_URL
 */

$showDelete ??= false;
$iconClass  = FileUploadHelper::iconClass($dok['mime_type']);
$iconColor  = FileUploadHelper::iconColor($dok['mime_type']);
$uploadedAt = isset($dok['created_at']) ? date('d/m/Y', strtotime($dok['created_at'])) : '';
?>
<div class="dok-detail-item" id="dok-wrap-<?= $dok['id'] ?? '' ?>">

  <i class="fas <?= $iconClass ?> dok-icon-big" style="color:<?= $iconColor ?>"></i>

  <div class="dok-info">
    <a href="<?= $baseUrl ?>/public/uploads/dokumen/<?= htmlspecialchars($dok['filename']) ?>"
       target="_blank" class="dok-filename">
      <?= htmlspecialchars($dok['original_name']) ?>
    </a>
    <span class="dok-meta">
      <?php if ($uploadedAt): ?>Diupload: <?= $uploadedAt ?> &nbsp;·&nbsp;<?php endif; ?>
      <?= htmlspecialchars($dok['mime_type']) ?>
    </span>
  </div>

  <a href="<?= $baseUrl ?>/public/uploads/dokumen/<?= htmlspecialchars($dok['filename']) ?>"
     download="<?= htmlspecialchars($dok['original_name']) ?>"
     class="btn-dl">
    <i class="fas fa-download"></i> Unduh
  </a>

  <?php if ($showDelete && isset($dok['id'])): ?>
  <label style="cursor:pointer;display:flex;align-items:center;gap:4px;font-size:12px;color:var(--danger);margin-left:8px">
    <input type="checkbox" name="hapus_dokumen[]" value="<?= $dok['id'] ?>"
           onchange="toggleHapusDok(this, <?= $dok['id'] ?>)">
    Hapus
  </label>
  <?php endif; ?>

</div>