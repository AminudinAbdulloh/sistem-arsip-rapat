<?php
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$maxStat = 0;
foreach ($monthlyStats as $s) { if ($s['total'] > $maxStat) $maxStat = $s['total']; }
$months = array_column($monthlyStats, 'total', 'bulan');
?>
<div class="page-header">
  <div class="breadcrumb"><i class="fas fa-home"></i> Dashboard</div>
  <h1><i class="fas fa-tachometer-alt" style="color:var(--primary-light)"></i> Dashboard</h1>
  <p>Selamat datang, <?= htmlspecialchars($_SESSION['user_nama'] ?? '') ?>! Berikut ringkasan data arsip rapat.</p>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-envelope-open-text"></i></div>
    <div class="stat-info">
      <div class="label">Total Undangan Rapat</div>
      <div class="value"><?= $totalUndangan ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-file-alt"></i></div>
    <div class="stat-info">
      <div class="label">Total Notulensi Rapat</div>
      <div class="value"><?= $totalNotulensi ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fas fa-calendar-alt"></i></div>
    <div class="stat-info">
      <div class="label">Tahun Aktif</div>
      <div class="value"><?= $selectedYear ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-calendar-check"></i></div>
    <div class="stat-info">
      <div class="label">Bulan Ini</div>
      <div class="value"><?= $months[date('n')] ?? 0 ?></div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px" class="dashboard-grid">
  <!-- Laporan Bulanan -->
  <div class="card">
    <div class="card-header">
      <h2><i class="fas fa-calendar-alt" style="color:var(--primary-light)"></i> Laporan Bulanan</h2>
    </div>
    <div class="card-body">
      <form method="GET" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:16px">
        <input type="hidden" name="url" value="dashboard">
        <div>
          <label class="form-label">Tahun</label>
          <select name="year" class="form-control" style="width:120px">
            <?php if (empty($availableYears)): ?>
              <option><?= date('Y') ?></option>
            <?php else: ?>
              <?php foreach ($availableYears as $y): ?>
                <option value="<?= $y['tahun'] ?>" <?= $y['tahun'] == $selectedYear ? 'selected' : '' ?>><?= $y['tahun'] ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Bulan</label>
          <select name="month" class="form-control" style="width:140px">
            <?php for ($m=1; $m<=12; $m++): ?>
              <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>><?= $namaBulan[$m] ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <a href="<?= BASE_URL ?>/index.php?url=dashboard/downloadBulanan&year=<?= $selectedYear ?>&month=<?= $selectedMonth ?>" 
           target="_blank" class="btn btn-primary">
          <i class="fas fa-download"></i> Unduh PDF
        </a>
      </form>
      <p style="font-size:13px;color:var(--muted)">
        Laporan bulan <strong><?= $namaBulan[(int)$selectedMonth] ?> <?= $selectedYear ?></strong>
      </p>
    </div>
  </div>

  <!-- Laporan Tahunan -->
  <div class="card">
    <div class="card-header">
      <h2><i class="fas fa-chart-bar" style="color:var(--success)"></i> Laporan Tahunan</h2>
    </div>
    <div class="card-body">
      <form method="GET" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:16px">
        <input type="hidden" name="url" value="dashboard">
        <div>
          <label class="form-label">Tahun</label>
          <select name="yearAnnual" class="form-control" style="width:120px">
            <?php if (empty($availableYears)): ?>
              <option><?= date('Y') ?></option>
            <?php else: ?>
              <?php foreach ($availableYears as $y): ?>
                <option value="<?= $y['tahun'] ?>"><?= $y['tahun'] ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <a href="#" id="btnDownloadTahunan" class="btn btn-success">
          <i class="fas fa-download"></i> Unduh PDF
        </a>
      </form>
      <p style="font-size:13px;color:var(--muted)">Unduh laporan tahunan lengkap dalam format PDF.</p>
    </div>
  </div>
</div>

<!-- Grafik Statistik Bulanan -->
<div class="card">
  <div class="card-header">
    <h2><i class="fas fa-chart-line" style="color:var(--accent)"></i> Statistik Undangan Rapat per Bulan (<?= $selectedYear ?>)</h2>
  </div>
  <div class="card-body">
    <?php if (empty($monthlyStats)): ?>
      <div class="alert alert-info"><i class="fas fa-info-circle"></i> Belum ada data undangan rapat untuk tahun <?= $selectedYear ?>.</div>
    <?php else: ?>
    <div class="chart-bar-wrap">
      <?php for ($m = 1; $m <= 12; $m++):
        $count = $months[$m] ?? 0;
        $pct = $maxStat > 0 ? round(($count / $maxStat) * 100) : 0;
      ?>
      <div class="chart-bar-row">
        <div class="chart-bar-label"><?= substr($namaBulan[$m], 0, 3) ?></div>
        <div class="chart-bar-track">
          <div class="chart-bar-fill <?= $count === 0 ? 'zero' : '' ?>" style="width:<?= max($pct, $count>0?5:0) ?>%">
            <?php if ($count > 0): ?><span><?= $count ?></span><?php endif; ?>
          </div>
        </div>
        <?php if ($count === 0): ?><span style="font-size:12px;color:var(--muted)">0</span><?php endif; ?>
      </div>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
@media (max-width: 768px) {
  .dashboard-grid { grid-template-columns: 1fr !important; }
}
</style>
<script>
document.getElementById('btnDownloadTahunan').addEventListener('click', function(e) {
  e.preventDefault();
  var year = document.querySelector('[name="yearAnnual"]').value;
  window.open('<?= BASE_URL ?>/index.php?url=dashboard/downloadTahunan&year=' + year, '_blank');
});
</script>