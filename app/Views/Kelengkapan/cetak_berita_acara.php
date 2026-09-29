<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Berita Acara - <?= esc($undangan['acara']) ?></title>
<style>
  body { font-family: 'Times New Roman', serif; margin: 50px; color: #222; line-height: 1.5; }
  .header { text-align: center; border-bottom: 3px double #222; padding-bottom: 12px; margin-bottom: 24px; }
  .header h1 { font-size: 18px; margin: 0; }
  .header h2 { font-size: 16px; margin: 4px 0 0; text-decoration: underline; }
  .header p { margin: 4px 0 0; font-size: 13px; }
  table.info td { padding: 2px 8px 2px 0; vertical-align: top; }
  h3 { font-size: 14px; margin: 20px 0 6px; }
  table.hadir { width: 100%; border-collapse: collapse; font-size: 13px; }
  table.hadir th, table.hadir td { border: 1px solid #444; padding: 5px 8px; text-align: left; }
  .footer { margin-top: 36px; font-size: 12px; color: #555; text-align: right; }
  @media print { body { margin: 20px; } }
</style>
</head>
<body>
<div class="header">
  <h1>PROGRAM STUDI ITD ADISUTJIPTO</h1>
  <h2>BERITA ACARA RAPAT</h2>
  <?php if (!empty($beritaAcara['nomor'])): ?><p>Nomor: <?= esc($beritaAcara['nomor']) ?></p><?php endif; ?>
</div>

<table class="info">
  <tr><td>Acara</td><td>:</td><td><?= esc($undangan['acara']) ?></td></tr>
  <tr><td>Hari / Tanggal</td><td>:</td><td><?= esc($undangan['hari']) ?>, <?= date('d/m/Y', strtotime($undangan['waktu'])) ?></td></tr>
  <tr><td>Waktu</td><td>:</td><td><?= date('H.i', strtotime($undangan['waktu'])) ?> WIB</td></tr>
  <tr><td>Tempat</td><td>:</td><td><?= esc($undangan['tempat']) ?></td></tr>
</table>

<h3>Uraian</h3>
<div><?= nl2br(esc($beritaAcara['uraian'])) ?></div>

<?php if (!empty($beritaAcara['keputusan'])): ?>
  <h3>Keputusan / Kesimpulan</h3>
  <div><?= nl2br(esc($beritaAcara['keputusan'])) ?></div>
<?php endif; ?>

<h3>Daftar Hadir</h3>
<?php if (empty($hadir)): ?>
  <p>Belum ada daftar hadir.</p>
<?php else: ?>
  <table class="hadir">
    <thead><tr><th>No</th><th>Nama</th><th>Jabatan</th><th>Status</th><th>Keterangan</th></tr></thead>
    <tbody>
      <?php foreach ($hadir as $i => $h): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= esc($h['nama']) ?></td>
          <td><?= esc($h['jabatan'] ?? '') ?></td>
          <td><?= esc(\App\Models\DaftarHadirModel::STATUS_LABELS[$h['status']] ?? $h['status']) ?></td>
          <td><?= esc($h['keterangan'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<div class="footer">Dicetak oleh <?= esc($pencetak) ?> pada <?= date('d/m/Y H:i') ?></div>
<script>window.onload = function () { window.print(); }</script>
</body>
</html>
