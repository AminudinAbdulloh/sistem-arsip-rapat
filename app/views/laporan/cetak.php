<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($judul) ?></title>
<style>
  @page { margin: 1.5cm; }
  @media print {
    body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
  body       { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
  h1         { color: #1a3a5c; text-align: center; border-bottom: 2px solid #1a3a5c; padding-bottom: 10px; margin-bottom: 4px; }
  h2         { color: #2563eb; margin-top: 24px; margin-bottom: 8px; font-size: 14px; }
  .subtitle  { text-align: center; color: #555; margin: 4px 0; font-size: 11px; }
  table      { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th         { background: #1a3a5c; color: white; padding: 8px 10px; text-align: left; font-size: 12px; }
  td         { padding: 8px 10px; border-bottom: 1px solid #ddd; vertical-align: top; font-size: 12px; }
  tr:nth-child(even) td { background: #f0f4ff; }
  .pre-wrap  { white-space: pre-wrap; line-height: 1.5; }
  .no-data   { font-style: italic; color: #888; padding: 12px 0; }
</style>
</head>
<body>

<h1><?= htmlspecialchars($judul) ?></h1>
<p class="subtitle">Institut Teknologi Dirgantara Adisutjipto &mdash; Sistem Informasi Pengelolaan Arsip Rapat</p>
<p class="subtitle">Dicetak: <?= htmlspecialchars($cetakWaktu) ?></p>

<!-- ==================== UNDANGAN ==================== -->
<h2>Data Undangan Rapat (<?= count($undangan) ?> data)</h2>

<?php if (empty($undangan)): ?>
  <p class="no-data">Tidak ada data undangan rapat.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th width="30">No</th>
      <th width="160">Hari / Tanggal</th>
      <th width="80">Waktu</th>
      <th width="160">Tempat</th>
      <th>Agenda / Perihal</th>
      <th width="110">Tgl Surat</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($undangan as $i => $u):
      $ts      = strtotime($u['waktu']);
      $hariArr = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
      $hariTgl = $hariArr[(int) date('N', $ts)]
               . ', ' . (int) date('j', $ts)
               . ' ' . $namaBulan[(int) date('n', $ts)]
               . ' ' . date('Y', $ts);

      $tglSurat = '';
      if (!empty($u['tgl_surat'])) {
          $ts2      = strtotime($u['tgl_surat']);
          $tglSurat = (int) date('j', $ts2) . ' ' . $namaBulan[(int) date('n', $ts2)] . ' ' . date('Y', $ts2);
      }
    ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= htmlspecialchars($hariTgl) ?></td>
      <td><?= date('H:i', $ts) ?> WIB</td>
      <td><?= htmlspecialchars($u['tempat']) ?></td>
      <td class="pre-wrap"><?= htmlspecialchars($u['acara']) ?></td>
      <td><?= htmlspecialchars($tglSurat) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<!-- ==================== NOTULENSI ==================== -->
<h2>Data Notulensi Rapat (<?= count($notulensi) ?> data)</h2>

<?php if (empty($notulensi)): ?>
  <p class="no-data">Tidak ada data notulensi rapat.</p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <th width="30">No</th>
      <th width="140">Tanggal Rapat</th>
      <th width="180">Tema Rapat</th>
      <th>Deskripsi Rapat</th>
      <th width="180">Catatan</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($notulensi as $i => $n):
      $ts3      = strtotime($n['tgl_rapat']);
      $tglRapat = (int) date('j', $ts3) . ' ' . $namaBulan[(int) date('n', $ts3)] . ' ' . date('Y', $ts3);
    ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= htmlspecialchars($tglRapat) ?></td>
      <td><?= htmlspecialchars($n['tema_rapat']) ?></td>
      <td class="pre-wrap"><?= htmlspecialchars($n['deskripsi_rapat']) ?></td>
      <td class="pre-wrap"><?= htmlspecialchars($n['catatan'] ?? '-') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<script>
  window.onload = function () { window.print(); };
</script>
</body>
</html>