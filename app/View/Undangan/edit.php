<div class="page-header">
    <div>
        <h1><i class="fas fa-edit" style="color:var(--accent)"></i> Edit Undangan Rapat</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <a href="/undangan">Undangan Rapat</a>
            <i class="fas fa-chevron-right" style="font-size:10px"></i>
            <span>Edit</span>
        </div>
    </div>
</div>

<div class="card" style="max-width:700px">
    <div class="card-header">
        <h2><i class="fas fa-file-alt"></i> Form Edit Undangan Rapat</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="/undangan/<?= $undangan['id'] ?>/update">
            <div class="form-row">
                <div class="form-group">
                    <label for="hari">Hari <span class="required">*</span></label>
                    <select id="hari" name="hari" class="form-control" required>
                        <option value="">-- Pilih Hari --</option>
                        <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h): ?>
                            <option value="<?= $h ?>" <?= $undangan['hari'] === $h ? 'selected' : '' ?>><?= $h ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="waktu">Tanggal & Waktu <span class="required">*</span></label>
                    <input type="datetime-local" id="waktu" name="waktu" class="form-control" required
                           value="<?= date('Y-m-d\TH:i', strtotime($undangan['waktu'])) ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="tempat">Tempat <span class="required">*</span></label>
                <input type="text" id="tempat" name="tempat" class="form-control"
                       value="<?= htmlspecialchars($undangan['tempat']) ?>" required>
            </div>
            <div class="form-group">
                <label for="acara">Acara / Perihal <span class="required">*</span></label>
                <textarea id="acara" name="acara" class="form-control" rows="4" required><?= htmlspecialchars($undangan['acara']) ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="/undangan" class="btn btn-outline">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
