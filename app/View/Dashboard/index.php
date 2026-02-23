<?php
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<div class="page-header">
    <div>
        <h1><i class="fas fa-tachometer-alt" style="color:var(--accent)"></i> Dashboard</h1>
        <p class="breadcrumb">Selamat datang, <?= htmlspecialchars($_SESSION['user']['nama']) ?>!</p>
    </div>
    <div style="display:flex;gap:10px">
        <a href="/dashboard/download?type=bulanan&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-download"></i> Laporan Bulanan
        </a>
        <a href="/dashboard/download?type=tahunan&tahun=<?= $tahun ?>" class="btn btn-accent btn-sm">
            <i class="fas fa-download"></i> Laporan Tahunan
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card" style="margin-bottom:24px">
    <div class="card-body" style="padding:16px 24px">
        <form method="GET" action="/dashboard" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            <label style="font-size:13px;font-weight:600;color:var(--text-muted)"><i class="fas fa-filter"></i> Filter Periode:</label>
            <select name="bulan" class="form-control" style="width:auto;padding:8px 14px">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $bulan ? 'selected' : '' ?>><?= $namaBulan[$m] ?></option>
                <?php endfor; ?>
            </select>
            <select name="tahun" class="form-control" style="width:auto;padding:8px 14px">
                <?php foreach ($availableYears as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Tampilkan</button>
        </form>
    </div>
</div>

<!-- Stats bulanan -->
<h3 style="font-size:14px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px">
    <i class="fas fa-calendar-alt"></i> Laporan Bulan <?= $namaBulan[$bulan] ?> <?= $tahun ?>
</h3>
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-info">
            <div class="value"><?= $monthlyUndangan ?></div>
            <div class="label">Undangan Rapat Bulan Ini</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-clipboard-check"></i></div>
        <div class="stat-info">
            <div class="value"><?= $monthlyNotulensi ?></div>
            <div class="label">Notulensi Rapat Bulan Ini</div>
        </div>
    </div>
</div>

<!-- Stats tahunan -->
<h3 style="font-size:14px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px">
    <i class="fas fa-chart-bar"></i> Laporan Tahun <?= $tahun ?>
</h3>
<div class="stats-grid" style="margin-bottom:28px">
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-info">
            <div class="value"><?= $yearlyUndangan ?></div>
            <div class="label">Total Undangan Tahun <?= $tahun ?></div>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-clipboard-list"></i></div>
        <div class="stat-info">
            <div class="value"><?= $yearlyNotulensi ?></div>
            <div class="label">Total Notulensi Tahun <?= $tahun ?></div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-chart-line" style="color:var(--accent)"></i> Grafik Rapat Per Bulan - <?= $tahun ?></h2>
    </div>
    <div class="card-body">
        <canvas id="rapatChart" height="80"></canvas>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const chartData = <?= json_encode($chartData) ?>;
const labels = chartData.map(d => d.bulan);
const undanganData = chartData.map(d => d.undangan);
const notulensiData = chartData.map(d => d.notulensi);

new Chart(document.getElementById('rapatChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Undangan Rapat',
                data: undanganData,
                backgroundColor: 'rgba(49,130,206,0.75)',
                borderColor: '#3182ce',
                borderWidth: 2,
                borderRadius: 6,
            },
            {
                label: 'Notulensi Rapat',
                data: notulensiData,
                backgroundColor: 'rgba(56,161,105,0.75)',
                borderColor: '#38a169',
                borderWidth: 2,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f0f4f8' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
