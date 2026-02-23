<div class="page-header">
    <div>
        <h1><i class="fas fa-clipboard-check" style="color:var(--accent)"></i> Detail Notulensi Rapat</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="/notulensi">Notulensi Rapat</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <span>Detail</span>
        </div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="/notulensi/<?= $notulensi['id'] ?>/edit" class="btn btn-info btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="/notulensi" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start">
    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Informasi Notulensi</h2>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                    <div>
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px">Tanggal Rapat</div>
                        <div style="font-size:16px;font-weight:700;color:var(--primary)"><?= date('d F Y', strtotime($notulensi['tgl_rapat'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px">Tema Rapat</div>
                        <div style="font-size:15px;font-weight:600"><?= htmlspecialchars($notulensi['tema_rapat']) ?></div>
                    </div>
                </div>

                <div style="margin-bottom:20px">
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px">Deskripsi Rapat</div>
                    <div style="background:#f7fafc;border-left:4px solid var(--primary);padding:16px;border-radius:0 8px 8px 0;line-height:1.7;font-size:14px">
                        <?= nl2br(htmlspecialchars($notulensi['deskripsi_rapat'])) ?>
                    </div>
                </div>

                <?php if ($notulensi['catatan']): ?>
                <div>
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px">Catatan Tambahan</div>
                    <div style="background:#fffaf0;border-left:4px solid var(--accent);padding:16px;border-radius:0 8px 8px 0;line-height:1.7;font-size:14px">
                        <?= nl2br(htmlspecialchars($notulensi['catatan'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <h2><i class="fas fa-envelope-open-text"></i> Undangan Terkait</h2>
            </div>
            <div class="card-body">
                <div style="margin-bottom:12px">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase">Acara</div>
                    <div style="font-size:14px;font-weight:600;margin-top:2px"><?= htmlspecialchars($notulensi['nama_undangan']) ?></div>
                </div>
                <div style="margin-bottom:12px">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase">Waktu Undangan</div>
                    <div style="font-size:14px;margin-top:2px">
                        <?= htmlspecialchars($notulensi['hari']) ?>, <?= date('d F Y H:i', strtotime($notulensi['waktu_undangan'])) ?> WIB
                    </div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase">Tempat</div>
                    <div style="font-size:14px;margin-top:2px"><?= htmlspecialchars($notulensi['tempat']) ?></div>
                </div>
            </div>
        </div>

        <?php if ($notulensi['dokumentasi']): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-camera"></i> Dokumentasi</h2>
            </div>
            <div class="card-body" style="padding:12px">
                <img src="/uploads/dokumentasi/<?= htmlspecialchars($notulensi['dokumentasi']) ?>"
                     alt="Dokumentasi Rapat"
                     style="width:100%;border-radius:8px;cursor:pointer;transition:opacity 0.2s"
                     onmouseover="this.style.opacity='0.9'"
                     onmouseout="this.style.opacity='1'"
                     onclick="window.open(this.src,'_blank')">
                <p style="font-size:11px;color:var(--text-muted);text-align:center;margin-top:8px">
                    <i class="fas fa-external-link-alt"></i> Klik untuk lihat penuh
                </p>
            </div>
        </div>
        <?php endif; ?>

        <div class="card" style="margin-top:20px">
            <div class="card-body" style="padding:16px">
                <div style="font-size:11px;color:var(--text-muted);font-weight:700;text-transform:uppercase;margin-bottom:8px">Info Pencatatan</div>
                <div style="font-size:13px;color:var(--text-muted)">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($notulensi['created_by_nama']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
