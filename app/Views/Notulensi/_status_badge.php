<?php
$warna = [
    'menunggu'      => 'bg-yellow-100 text-yellow-800',
    'terverifikasi' => 'bg-green-100 text-green-800',
    'ditolak'       => 'bg-red-100 text-red-800',
][$status] ?? 'bg-gray-100 text-gray-800';
?>
<span class="px-2 py-1 rounded-full text-xs font-medium <?= $warna ?>"><?= esc(\App\Models\NotulensiRapatModel::STATUS_LABELS[$status] ?? $status) ?></span>
