<div class="page-header">
    <div>
        <h1><i class="fas fa-envelope-open-text" style="color:var(--accent)"></i> Undangan Rapat</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <span>Undangan Rapat</span>
        </div>
    </div>
    <a href="/undangan/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Undangan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Daftar Undangan Rapat</h2>
        <span class="badge badge-blue"><?= count($undangan) ?> undangan</span>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($undangan)): ?>
            <div class="empty-state">
                <i class="fas fa-envelope-open-text"></i>
                <h3>Belum Ada Undangan</h3>
                <p>Silakan tambahkan undangan rapat baru.</p>
            </div>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Tempat</th>
                        <th>Acara</th>
                        <th>Dibuat Oleh</th>
                        <th>Status</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($undangan as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <span class="badge badge-blue"><?= htmlspecialchars($u['hari']) ?></span>
                        </td>
                        <td>
                            <div style="font-weight:600"><?= date('d/m/Y', strtotime($u['waktu'])) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= date('H:i', strtotime($u['waktu'])) ?> WIB</div>
                        </td>
                        <td><?= htmlspecialchars($u['tempat']) ?></td>
                        <td>
                            <div style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($u['acara']) ?>">
                                <?= htmlspecialchars($u['acara']) ?>
                            </div>
                        </td>
                        <td style="font-size:13px;color:var(--text-muted)"><?= htmlspecialchars($u['created_by_nama']) ?></td>
                        <td>
                            <?php
                            $waktuRapat = strtotime($u['waktu']);
                            $now = time();
                            if ($waktuRapat > $now): ?>
                                <span class="badge badge-green">Mendatang</span>
                            <?php elseif ($waktuRapat > strtotime('-7 days')): ?>
                                <span class="badge badge-orange">Baru Berlalu</span>
                            <?php else: ?>
                                <span class="badge" style="background:#f7fafc;color:#a0aec0">Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns" style="justify-content:center">
                                <a href="/undangan/<?= $u['id'] ?>/download" class="btn btn-success btn-sm" title="Download PDF">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="/undangan/<?= $u['id'] ?>/edit" class="btn btn-info btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="/undangan/<?= $u['id'] ?>/delete" style="display:inline"
                                      onsubmit="return confirm('Hapus undangan ini?')">
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
