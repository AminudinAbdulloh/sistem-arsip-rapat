<?php
// Formatter untuk format lengkap (Hari, Tanggal Bulan Tahun)
$formatterLengkap = new IntlDateFormatter(
  'id_ID', 
  IntlDateFormatter::FULL, 
  IntlDateFormatter::NONE
);

// Formatter untuk tanggal saja (Tanpa Hari)
$formatterTanggal = new IntlDateFormatter(
  'id_ID', 
  IntlDateFormatter::LONG, 
  IntlDateFormatter::NONE
);
?>

<div class="page-header">
  <div class="breadcrumb"><i class="fas fa-home"></i> <a href="<?= BASE_URL ?>/index.php?url=dashboard">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:10px"></i> Undangan Rapat</div>
  <h1><i class="fas fa-envelope-open-text" style="color:var(--primary-light)"></i> Undangan Rapat</h1>
  <p>Kelola data undangan rapat program studi.</p>
</div>

<div class="card">
  <div class="card-header">
    <h2>Daftar Undangan Rapat</h2>
    <a href="<?= BASE_URL ?>/index.php?url=undangan/create" class="btn btn-primary">
      <i class="fas fa-plus"></i> Tambah Undangan
    </a>
  </div>
  <div class="table-wrap">
    <?php if (empty($undangan)): ?>
      <div style="padding:40px;text-align:center;color:var(--muted)">
        <i class="fas fa-inbox" style="font-size:48px;opacity:.3;display:block;margin-bottom:12px"></i>
        Belum ada data undangan rapat.
        <br><br>
        <a href="<?= BASE_URL ?>/index.php?url=undangan/create" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Pertama</a>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th width="50">No</th>
          <th>Hari/Tanggal Rapat</th>
          <th>Waktu Rapat</th>
          <th>Tempat</th>
          <th>Agenda</th>
          <th>Tanggal Surat</th>
          <th width="160">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($undangan as $i => $u): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
              <?php 
                  $dateWaktu = new DateTime($u['waktu']);
                  $formatterLengkap->setPattern("EEEE / d MMMM y"); 
                  echo $formatterLengkap->format($dateWaktu);
              ?>
          </td>
          <td><?= date('H:i', strtotime($u['waktu'])) ?></td>
          <td><?= htmlspecialchars($u['tempat']) ?></td>
          <td><?= htmlspecialchars(strlen($u['acara']) > 60 ? substr($u['acara'],0,60).'...' : $u['acara']) ?></td>
          <td>
              <?= $formatterTanggal->format(new DateTime($u['tgl_surat'])) ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap">
              <a href="<?= BASE_URL ?>/index.php?url=undangan/doc/<?= $u['id'] ?>"
                 class="btn btn-sm btn-success" title="Unduh Surat (.docx)">
                <i class="fas fa-file-word"></i>
              </a>
              <a href="<?= BASE_URL ?>/index.php?url=undangan/edit/<?= $u['id'] ?>" 
                 class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <button onclick="confirmDelete('<?= BASE_URL ?>/index.php?url=undangan/delete/<?= $u['id'] ?>', 'Hapus undangan rapat: <?= htmlspecialchars(addslashes($u['acara'])) ?>?')"
                      class="btn btn-sm btn-danger" title="Hapus">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>