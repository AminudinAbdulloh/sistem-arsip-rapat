<div class="page-header">
  <div class="breadcrumb"><i class="fas fa-home"></i> <a href="<?= BASE_URL ?>/index.php?url=dashboard">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:10px"></i> Notulensi Rapat</div>
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
        <a href="<?= BASE_URL ?>/index.php?url=notulensi/create" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Pertama</a>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th width="50">No</th>
          <th>Tgl Rapat</th>
          <th>Tema Rapat</th>
          <th>Acara Undangan</th>
          <th>Dokumentasi</th>
          <th>Dibuat Oleh</th>
          <th width="180">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($notulensi as $i => $n): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= date('d/m/Y', strtotime($n['tgl_rapat'])) ?></td>
          <td><strong><?= htmlspecialchars(strlen($n['tema_rapat']) > 40 ? substr($n['tema_rapat'],0,40).'...' : $n['tema_rapat']) ?></strong></td>
          <td><?= htmlspecialchars(strlen($n['nama_undangan']) > 50 ? substr($n['nama_undangan'],0,50).'...' : $n['nama_undangan']) ?></td>
          <td>
            <?php if ($n['dokumentasi']): ?>
              <a href="<?= BASE_URL ?>/public/uploads/dokumentasi/<?= htmlspecialchars($n['dokumentasi']) ?>" 
                 target="_blank" class="btn btn-sm btn-outline">
                <i class="fas fa-image"></i> Lihat
              </a>
            <?php else: ?>
              <span style="color:var(--muted);font-size:12px">—</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($n['pembuat']) ?></td>
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
              <button onclick="confirmDelete('<?= BASE_URL ?>/index.php?url=notulensi/delete/<?= $n['id'] ?>', 'Hapus notulensi: <?= htmlspecialchars(addslashes($n['tema_rapat'])) ?>?')"
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