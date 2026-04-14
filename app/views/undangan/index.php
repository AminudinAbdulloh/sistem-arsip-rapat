<?php
$formatterLengkap = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL,  IntlDateFormatter::NONE);
$formatterTanggal = new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE);

// Parameter untuk partial paginasi
$basePageUrl = $baseUrl . '/index.php?url=undangan';
?>

<div class="page-header">
  <div class="breadcrumb">
    <i class="fas fa-home"></i>
    <a href="<?= $baseUrl ?>/index.php?url=dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    Undangan Rapat
  </div>
  <h1><i class="fas fa-envelope-open-text" style="color:var(--primary-light)"></i> Undangan Rapat</h1>
  <p>Kelola data undangan rapat program studi.</p>
</div>

<div class="card">
  <div class="card-header">
    <h2>Daftar Undangan Rapat</h2>
    <a href="<?= $baseUrl ?>/index.php?url=undangan/create" class="btn btn-primary">
      <i class="fas fa-plus"></i> Tambah Undangan
    </a>
  </div>

  <div class="table-wrap">
    <?php if (empty($undangan)): ?>
      <div style="padding:40px;text-align:center;color:var(--muted)">
        <i class="fas fa-inbox" style="font-size:48px;opacity:.3;display:block;margin-bottom:12px"></i>
        Belum ada data undangan rapat.
        <br><br>
        <a href="<?= $baseUrl ?>/index.php?url=undangan/create" class="btn btn-primary">
          <i class="fas fa-plus"></i> Tambah Pertama
        </a>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th width="50">No</th>
          <th>Hari/Tanggal Rapat</th>
          <th width="80">Waktu</th>
          <th>Tempat</th>
          <th>Agenda</th>
          <th>Tanggal Surat</th>
          <th width="140">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Nomor urut dimulai dari baris pertama halaman ini
        $perPage   = 8;
        $startNo   = ($currentPage - 1) * $perPage + 1;
        foreach ($undangan as $i => $u):
        ?>
        <tr>
          <td><?= $startNo + $i ?></td>
          <td>
            <?php
              $dateWaktu = new DateTime($u['waktu']);
              $formatterLengkap->setPattern('EEEE / d MMMM y');
              echo $formatterLengkap->format($dateWaktu);
            ?>
          </td>
          <td><?= date('H:i', strtotime($u['waktu'])) ?></td>
          <td><?= htmlspecialchars($u['tempat']) ?></td>
          <td><?= htmlspecialchars(mb_strlen($u['acara']) > 60 ? mb_substr($u['acara'], 0, 60) . '…' : $u['acara']) ?></td>
          <td><?= $formatterTanggal->format(new DateTime($u['tgl_surat'])) ?></td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap">
              <a href="<?= $baseUrl ?>/index.php?url=undangan/doc/<?= $u['id'] ?>"
                 class="btn btn-sm btn-success" title="Unduh Surat (.docx)">
                <i class="fas fa-file-word"></i>
              </a>
              <a href="<?= $baseUrl ?>/index.php?url=undangan/edit/<?= $u['id'] ?>"
                 class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <button onclick="confirmDelete(
                        '<?= $baseUrl ?>/index.php?url=undangan/delete/<?= $u['id'] ?>',
                        'Hapus undangan rapat: <?= htmlspecialchars(addslashes($u['acara'])) ?>?')"
                      class="btn btn-sm btn-danger" title="Hapus">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Paginasi -->
    <?php include BASE_PATH . '/app/views/partials/_pagination.php'; ?>

    <?php endif; ?>
  </div>
</div>