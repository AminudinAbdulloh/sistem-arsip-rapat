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
    Notulensi Rapat
  </div>
  <h1><i class="fas fa-file-alt" style="color:var(--primary-light)"></i> Notulensi Rapat</h1>
  <p>Kelola data notulensi rapat program studi.</p>
</div>

<div class="card">
  <div class="card-header">
    <h2>Daftar Notulensi Rapat</h2>
    <a href="<?= BASE_URL ?>/index.php?url=notulensi/create" class="btn btn-primary">
      <i class="fas fa-plus"></i> Tambah Notulensi
    </a>
  </div>
  <div class="table-wrap">
    <?php if (empty($notulensi)): ?>
      <div style="padding:40px;text-align:center;color:var(--muted)">
        <i class="fas fa-inbox" style="font-size:48px;opacity:.3;display:block;margin-bottom:12px"></i>
        Belum ada data notulensi rapat.
        <br><br>
        <a href="<?= BASE_URL ?>/index.php?url=notulensi/create" class="btn btn-primary">
          <i class="fas fa-plus"></i> Tambah Pertama
        </a>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th width="50">No</th>
          <th>Hari/Tanggal Rapat</th>
          <th>Tema Rapat</th>
          <th width="110">Foto</th>
          <th width="150">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($notulensi as $i => $n): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
            <?php
              $dateWaktu = new DateTime($n['tgl_rapat']);
              $formatterLengkap->setPattern("EEEE / d MMMM y");
              echo $formatterLengkap->format($dateWaktu);
            ?>
          </td>
          <td>
            <strong><?= htmlspecialchars(strlen($n['tema_rapat']) > 50 ? substr($n['tema_rapat'], 0, 50).'…' : $n['tema_rapat']) ?></strong>
          </td>
          <td>
            <?php if ($n['dokumentasi_preview']): ?>
              <a href="<?= BASE_URL ?>/index.php?url=notulensi/detail/<?= $n['id'] ?>"
                 title="Lihat detail foto">
                <img src="<?= BASE_URL ?>/public/uploads/dokumentasi/<?= htmlspecialchars($n['dokumentasi_preview']) ?>"
                     style="width:52px;height:40px;object-fit:cover;border-radius:5px;border:1px solid var(--border)"
                     alt="Foto">
              </a>
              <?php if ($n['dokumentasi_count'] > 1): ?>
                <span style="font-size:11px;color:var(--muted);display:block;margin-top:2px">
                  +<?= $n['dokumentasi_count'] - 1 ?> foto
                </span>
              <?php endif; ?>
            <?php else: ?>
              <span style="color:var(--muted);font-size:12px">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap">
              <a href="<?= BASE_URL ?>/index.php?url=notulensi/detail/<?= $n['id'] ?>"
                 class="btn btn-sm btn-primary" title="Detail">
                <i class="fas fa-eye"></i>
              </a>
              <a href="<?= BASE_URL ?>/index.php?url=notulensi/edit/<?= $n['id'] ?>"
                 class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <button onclick="confirmDelete('<?= BASE_URL ?>/index.php?url=notulensi/delete/<?= $n['id'] ?>',
                      'Hapus notulensi: <?= htmlspecialchars(addslashes($n['tema_rapat'])) ?>?')"
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