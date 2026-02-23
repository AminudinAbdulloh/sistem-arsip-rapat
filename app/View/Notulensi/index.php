<div class="page-header">
    <div>
        <h1><i class="fas fa-clipboard-list" style="color:var(--accent)"></i> Notulensi Rapat</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <span>Notulensi Rapat</span>
        </div>
    </div>
    <a href="/notulensi/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Notulensi
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Daftar Notulensi Rapat</h2>
        <span class="badge badge-green"><?= count($notulensi) ?> notulensi</span>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($notulensi)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>Belum Ada Notulensi</h3>
                <p>Silakan tambahkan notulensi rapat baru setelah rapat berlangsung.</p>
            </div>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tgl Rapat</th>
                        <th>Tema Rapat</th>
                        <th>Undangan Terkait</th>
                        <th>Dokumentasi</th>
                        <th>Dibuat Oleh</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notulensi as $i => $n): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <div style="font-weight:600"><?= date('d/m/Y', strtotime($n['tgl_rapat'])) ?></div>
                        </td>
                        <td>
                            <div style="font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($n['tema_rapat']) ?>">
                                <?= htmlspecialchars($n['tema_rapat']) ?>
                            </div>
                            <?php if ($n['catatan']): ?>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                                    <i class="fas fa-sticky-note"></i> Ada catatan
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px" title="<?= htmlspecialchars($n['nama_undangan']) ?>">
                                <?= htmlspecialchars($n['nama_undangan']) ?>
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($n['tempat']) ?></div>
                        </td>
                        <td>
                            <?php if ($n['dokumentasi']): ?>
                                <img src="/uploads/dokumentasi/<?= htmlspecialchars($n['dokumentasi']) ?>"
                                     alt="Dokumentasi" class="img-thumb"
                                     onclick="showImageModal(this.src)" style="cursor:pointer">
                            <?php else: ?>
                                <span style="font-size:12px;color:#a0aec0"><i class="fas fa-image"></i> -</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:var(--text-muted)"><?= htmlspecialchars($n['created_by_nama']) ?></td>
                        <td>
                            <div class="action-btns" style="justify-content:center">
                                <a href="/notulensi/<?= $n['id'] ?>/show" class="btn btn-accent btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/notulensi/<?= $n['id'] ?>/edit" class="btn btn-info btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="/notulensi/<?= $n['id'] ?>/delete" style="display:inline"
                                      onsubmit="return confirm('Hapus notulensi ini?')">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal untuk gambar -->
<div id="imageModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:1000;align-items:center;justify-content:center;cursor:pointer" onclick="this.style.display='none'">
    <img id="modalImage" src="" alt="" style="max-width:90%;max-height:90%;border-radius:12px;box-shadow:0 0 40px rgba(0,0,0,0.5)">
</div>

<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'flex';
}
</script>
