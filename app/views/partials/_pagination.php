<?php
/**
 * Partial: _pagination.php
 *
 * Variabel yang dibutuhkan (di-extract dari parent):
 *   int    $currentPage  — halaman aktif
 *   int    $totalPages   — total halaman
 *   int    $total        — total seluruh data
 *   string $basePageUrl  — URL dasar tanpa parameter ?page=  (mis. BASE_URL . '/index.php?url=undangan')
 *
 * Opsional:
 *   string $extraParams  — query string tambahan yang dipertahankan (mis. '&year=2025')
 */

$extraParams ??= '';

if ($totalPages <= 1 && $total === 0) return;   // sembunyikan jika tidak ada data sama sekali

// Rentang tombol halaman yang ditampilkan (maks 5 nomor di sekitar halaman aktif)
$range  = 2;
$start  = max(1, $currentPage - $range);
$end    = min($totalPages, $currentPage + $range);

// Hitung nomor baris pertama & terakhir di halaman ini
$perPage   = 8;
$firstItem = ($currentPage - 1) * $perPage + 1;
$lastItem  = min($currentPage * $perPage, $total);
?>

<div class="pagination-wrap">
  <!-- Info baris -->
  <span class="pagination-info">
    Menampilkan <strong><?= $firstItem ?>–<?= $lastItem ?></strong> dari <strong><?= $total ?></strong> data
  </span>

  <?php if ($totalPages > 1): ?>
  <nav class="pagination" aria-label="Navigasi halaman">

    <!-- Tombol Sebelumnya -->
    <?php if ($currentPage > 1): ?>
      <a href="<?= $basePageUrl ?>&page=<?= $currentPage - 1 ?><?= $extraParams ?>"
         class="pg-btn" title="Halaman sebelumnya">
        <i class="fas fa-chevron-left"></i>
      </a>
    <?php else: ?>
      <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i></span>
    <?php endif; ?>

    <!-- Halaman pertama + ellipsis -->
    <?php if ($start > 1): ?>
      <a href="<?= $basePageUrl ?>&page=1<?= $extraParams ?>" class="pg-btn">1</a>
      <?php if ($start > 2): ?>
        <span class="pg-ellipsis">&hellip;</span>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Nomor halaman -->
    <?php for ($p = $start; $p <= $end; $p++): ?>
      <?php if ($p === $currentPage): ?>
        <span class="pg-btn active" aria-current="page"><?= $p ?></span>
      <?php else: ?>
        <a href="<?= $basePageUrl ?>&page=<?= $p ?><?= $extraParams ?>"
           class="pg-btn"><?= $p ?></a>
      <?php endif; ?>
    <?php endfor; ?>

    <!-- Ellipsis + halaman terakhir -->
    <?php if ($end < $totalPages): ?>
      <?php if ($end < $totalPages - 1): ?>
        <span class="pg-ellipsis">&hellip;</span>
      <?php endif; ?>
      <a href="<?= $basePageUrl ?>&page=<?= $totalPages ?><?= $extraParams ?>"
         class="pg-btn"><?= $totalPages ?></a>
    <?php endif; ?>

    <!-- Tombol Berikutnya -->
    <?php if ($currentPage < $totalPages): ?>
      <a href="<?= $basePageUrl ?>&page=<?= $currentPage + 1 ?><?= $extraParams ?>"
         class="pg-btn" title="Halaman berikutnya">
        <i class="fas fa-chevron-right"></i>
      </a>
    <?php else: ?>
      <span class="pg-btn disabled"><i class="fas fa-chevron-right"></i></span>
    <?php endif; ?>

  </nav>
  <?php endif; ?>
</div>

<style>
  .pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    background: #fafbfc;
  }
  .pagination-info {
    font-size: 13px;
    color: var(--muted);
  }
  .pagination {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
  }
  .pg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 8px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    border: 1.5px solid var(--border);
    color: var(--text);
    background: white;
    transition: all .15s;
    cursor: pointer;
  }
  .pg-btn:hover:not(.active):not(.disabled) {
    border-color: var(--primary-light);
    color: var(--primary-light);
    background: #eff6ff;
  }
  .pg-btn.active {
    background: var(--primary-light);
    border-color: var(--primary-light);
    color: white;
    font-weight: 700;
    cursor: default;
  }
  .pg-btn.disabled {
    opacity: .38;
    cursor: not-allowed;
    pointer-events: none;
  }
  .pg-ellipsis {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 34px;
    font-size: 14px;
    color: var(--muted);
    letter-spacing: 1px;
  }
  @media (max-width: 480px) {
    .pagination-wrap { justify-content: center; }
    .pagination-info { width: 100%; text-align: center; }
  }
</style>