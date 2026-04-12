<?php
// Formatter untuk format lengkap (Hari, Tanggal Bulan Tahun)
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
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="detail-grid">
      <div>
        <h3 style="font-size:14px;color:var(--muted);margin-bottom:16px;text-transform:uppercase;letter-spacing:.5px">Informasi Rapat</h3>
        <table style="width:100%;font-size:14px;">
          <tr>
            <td style="padding:8px 0;color:var(--muted);width:140px"><i class="fas fa-calendar" style="color:var(--primary-light)"></i> <strong>Hari/Tanggal Rapat</strong></td>
            <td style="padding:8px 0"><strong>
              <?php 
                    $dateWaktu = new DateTime($notulensi['tgl_rapat']);
                    $formatterLengkap->setPattern("EEEE / d MMMM y"); 
                    echo $formatterLengkap->format($dateWaktu);
                ?>
            </strong></td>
          </tr>
          <tr>
            <td style="padding:8px 0;color:var(--muted)"><i class="fas fa-clipboard" style="color:var(--warning)"></i> <strong>Tema Rapat</strong></td>
            <td style="padding:8px 0"><strong><?= htmlspecialchars($notulensi['tema_rapat']) ?></strong></td>
          </tr>
          <tr>
            <td style="padding:8px 0;color:var(--muted)"><i class="fas fa-map-marker-alt" style="color:var(--danger)"></i> <strong>Tempat Rapat</strong></td>
            <td style="padding:8px 0"><strong><?= htmlspecialchars($notulensi['tempat']) ?></strong></td>
          </tr>
        </table>

        <h3 style="font-size:14px;color:var(--muted);margin:20px 0 12px;text-transform:uppercase;letter-spacing:.5px">Dokumen Pendukung</h3>
        <div style="background:#f8fafc;border-radius:8px;padding:14px;border:1px solid var(--border)">
        </div>
      </div>
      <div>
        <?php if ($notulensi['dokumentasi']): ?>
        <h3 style="font-size:14px;color:var(--muted);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px">Dokumentasi</h3>
        <img src="<?= BASE_URL ?>/public/uploads/dokumentasi/<?= htmlspecialchars($notulensi['dokumentasi']) ?>"
             style="width:100%;max-height:250px;object-fit:cover;border-radius:10px;border:2px solid var(--border)" alt="Dokumentasi">
        <?php else: ?>
        <div style="background:#f8fafc;border-radius:8px;padding:40px;text-align:center;color:var(--muted);border:2px dashed var(--border)">
          <i class="fas fa-image" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px"></i>
          Tidak ada dokumentasi foto
        </div>
        <?php endif; ?>
      </div>
    </div>

    <hr style="border:none;border-top:1px solid var(--border);margin:24px 0">

    <div class="form-group">
      <label class="form-label" style="font-size:15px">Deskripsi Rapat</label>
      <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid var(--border);font-size:14px;line-height:1.7;white-space:pre-wrap"><?= htmlspecialchars($notulensi['deskripsi_rapat']) ?></div>
    </div>

    <?php if ($notulensi['catatan']): ?>
    <div class="form-group">
      <label class="form-label" style="font-size:15px">Catatan Tambahan</label>
      <div style="background:#fffbeb;padding:16px;border-radius:8px;border:1px solid #fcd34d;font-size:14px;line-height:1.7;white-space:pre-wrap"><?= htmlspecialchars($notulensi['catatan']) ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
@media (max-width:768px) { .detail-grid { grid-template-columns: 1fr !important; } }
</style>