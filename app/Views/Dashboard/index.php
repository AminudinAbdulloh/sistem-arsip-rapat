<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="/dashboard" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select name="bulan" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $bulan ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?= $year ?>" <?= $year == $tahun ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-[#1e3a5f] text-white px-4 py-2 rounded-lg hover:bg-[#2d5f8f] transition-colors">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Undangan Bulan Ini</p>
                    <p class="text-3xl font-bold text-[#1e3a5f]"><?= $monthlyUndangan ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope text-[#1e3a5f] text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Notulensi Bulan Ini</p>
                    <p class="text-3xl font-bold text-green-600"><?= $monthlyNotulensi ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Undangan Tahun <?= $tahun ?></p>
                    <p class="text-3xl font-bold text-purple-600"><?= $yearlyUndangan ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Notulensi Tahun <?= $tahun ?></p>
                    <p class="text-3xl font-bold text-orange-600"><?= $yearlyNotulensi ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Grafik Arsip Rapat Tahun <?= $tahun ?></h3>
            <div class="flex gap-2">
                <a href="/dashboard/download?type=bulanan&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="bg-[#1e3a5f] text-white px-4 py-2 rounded-lg hover:bg-[#2d5f8f] transition-colors text-sm">
                    <i class="fas fa-download mr-2"></i>Laporan Bulanan
                </a>
                <a href="/dashboard/download?type=tahunan&tahun=<?= $tahun ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                    <i class="fas fa-download mr-2"></i>Laporan Tahunan
                </a>
            </div>
        </div>
        <div class="h-80">
            <canvas id="chartArsip"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('chartArsip').getContext('2d');
const chartData = <?= json_encode($chartData) ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.bulan),
        datasets: [
            {
                label: 'Undangan Rapat',
                data: chartData.map(d => d.undangan),
                backgroundColor: '#1e3a5f',
                borderRadius: 4,
            },
            {
                label: 'Notulensi Rapat',
                data: chartData.map(d => d.notulensi),
                backgroundColor: '#10b981',
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
<?= $this->endSection() ?>
