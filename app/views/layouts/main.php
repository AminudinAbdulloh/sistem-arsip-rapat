<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? 'Arsip Rapat') ?> - ITD Adisutjipto</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  :root {
    --primary: #1a3a5c;
    --primary-light: #2563eb;
    --primary-dark: #0f2644;
    --accent: #f59e0b;
    --sidebar-w: 260px;
    --navbar-h: 64px;
    --bg: #f0f4f8;
    --card-bg: #ffffff;
    --text: #1f2937;
    --muted: #6b7280;
    --border: #e5e7eb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; background: var(--bg); color: var(--text); }

  /* NAVBAR */
  .navbar {
    position: fixed; top: 0; left: 0; right: 0; height: var(--navbar-h);
    background: var(--primary);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 20px 0 calc(var(--sidebar-w) + 20px);
    z-index: 100;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
  }
  .navbar-user { display: flex; align-items: center; gap: 12px; color: white; }
  .navbar-user img, .navbar-user .avatar {
    width: 38px; height: 38px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3);
    object-fit: cover;
  }
  .avatar {
    background: var(--primary-light); color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700;
  }
  .navbar-user-info { text-align: right; }
  .navbar-user-info .name { font-weight: 600; font-size: 14px; }
  .navbar-user-info .nip { font-size: 11px; opacity: .7; }
  .hamburger {
    display: none; background: none; border: none; color: white; font-size: 20px; cursor: pointer;
  }

  /* SIDEBAR */
  .sidebar {
    position: fixed; top: 0; left: 0; width: var(--sidebar-w);
    height: 100vh; background: var(--primary-dark);
    display: flex; flex-direction: column;
    z-index: 200;
    transition: transform .3s;
  }
  .sidebar-logo {
    padding: 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex; align-items: center; gap: 12px; color: white;
    height: var(--navbar-h);
  }
  .sidebar-logo .logo-icon {
    width: 38px; height: 38px; background: var(--primary-light);
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: white; flex-shrink: 0;
  }
  .sidebar-logo span { font-weight: 700; font-size: 14px; line-height: 1.3; }
  .sidebar-logo span small { display: block; font-size: 10px; opacity: .6; font-weight: 400; }
  .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
  .nav-section { margin-bottom: 8px; }
  .nav-section-title {
    font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.35);
    text-transform: uppercase; letter-spacing: 1px;
    padding: 6px 8px; margin-bottom: 4px;
  }
  .nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px; border-radius: 8px;
    color: rgba(255,255,255,0.7); text-decoration: none;
    font-size: 14px; transition: all .2s; margin-bottom: 2px;
  }
  .nav-item:hover { background: rgba(255,255,255,0.08); color: white; }
  .nav-item.active { background: var(--primary-light); color: white; font-weight: 600; }
  .nav-item i { width: 18px; text-align: center; font-size: 15px; }
  .sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid rgba(255,255,255,0.1);
  }
  .nav-logout {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px; border-radius: 8px;
    color: rgba(255,100,100,0.8); text-decoration: none;
    font-size: 14px; transition: all .2s;
  }
  .nav-logout:hover { background: rgba(239,68,68,0.15); color: #f87171; }

  /* MAIN */
  .main {
    margin-left: var(--sidebar-w);
    padding-top: var(--navbar-h);
    min-height: 100vh;
  }
  .page-content { padding: 28px 28px; }
  .page-header { margin-bottom: 24px; }
  .page-header h1 { font-size: 22px; font-weight: 700; color: var(--primary); }
  .page-header p { color: var(--muted); font-size: 14px; margin-top: 4px; }
  .breadcrumb { display: flex; gap: 8px; align-items: center; font-size: 13px; color: var(--muted); margin-bottom: 6px; }
  .breadcrumb a { color: var(--primary-light); text-decoration: none; }

  /* CARDS */
  .card {
    background: var(--card-bg); border-radius: 12px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.07);
    overflow: hidden;
  }
  .card-header {
    padding: 18px 24px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
  }
  .card-header h2 { font-size: 16px; font-weight: 700; color: var(--primary); }
  .card-body { padding: 24px; }

  /* STAT CARDS */
  .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .stat-card {
    background: var(--card-bg); border-radius: 12px;
    padding: 20px 24px; box-shadow: 0 1px 8px rgba(0,0,0,0.07);
    display: flex; align-items: center; gap: 16px;
  }
  .stat-icon {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
  }
  .stat-icon.blue { background: #eff6ff; color: var(--primary-light); }
  .stat-icon.green { background: #ecfdf5; color: var(--success); }
  .stat-icon.amber { background: #fffbeb; color: var(--warning); }
  .stat-icon.red { background: #fef2f2; color: var(--danger); }
  .stat-info .label { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
  .stat-info .value { font-size: 28px; font-weight: 700; color: var(--primary); }

  /* TABLE */
  .table-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #f8fafc; color: var(--primary); font-size: 13px; font-weight: 700;
       padding: 12px 16px; text-align: left; border-bottom: 2px solid var(--border); }
  td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 14px; vertical-align: middle; }
  tr:hover td { background: #f8fafc; }
  tr:last-child td { border-bottom: none; }

  /* BUTTONS */
  .btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    border: none; cursor: pointer; text-decoration: none; transition: all .2s;
  }
  .btn-primary { background: var(--primary-light); color: white; }
  .btn-primary:hover { background: #1d4ed8; }
  .btn-success { background: var(--success); color: white; }
  .btn-success:hover { background: #059669; }
  .btn-warning { background: var(--warning); color: white; }
  .btn-warning:hover { background: #d97706; }
  .btn-danger { background: var(--danger); color: white; }
  .btn-danger:hover { background: #dc2626; }
  .btn-secondary { background: #6b7280; color: white; }
  .btn-secondary:hover { background: #4b5563; }
  .btn-outline { background: transparent; border: 2px solid var(--border); color: var(--text); }
  .btn-outline:hover { border-color: var(--primary-light); color: var(--primary-light); }
  .btn-sm { padding: 5px 10px; font-size: 12px; }

  /* FORMS */
  .form-group { margin-bottom: 20px; }
  .form-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .form-label span.req { color: var(--danger); margin-left: 2px; }
  .form-control {
    width: 100%; padding: 10px 14px;
    border: 2px solid var(--border); border-radius: 8px;
    font-size: 14px; outline: none; transition: border-color .2s;
    font-family: inherit;
  }
  .form-control:focus { border-color: var(--primary-light); }
  select.form-control { cursor: pointer; }
  textarea.form-control { resize: vertical; min-height: 100px; }
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .form-hint { font-size: 12px; color: var(--muted); margin-top: 4px; }

  /* ALERTS */
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; gap: 10px; align-items: flex-start; }
  .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
  .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
  .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }
  .alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

  /* BADGE */
  .badge {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
  }
  .badge-blue { background: #dbeafe; color: #1d4ed8; }
  .badge-green { background: #d1fae5; color: #065f46; }

  /* MODAL */
  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 1000;
    align-items: center; justify-content: center;
  }
  .modal-overlay.show { display: flex; }
  .modal {
    background: white; border-radius: 12px; padding: 28px;
    max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  }
  .modal h3 { margin-bottom: 10px; color: var(--primary); }
  .modal p { color: var(--muted); font-size: 14px; margin-bottom: 20px; }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }

  /* OVERLAY for mobile */
  .sidebar-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 150;
  }
  .sidebar-overlay.show { display: block; }

  /* CHART */
  .chart-bar-wrap { margin-top: 8px; }
  .chart-bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 13px; }
  .chart-bar-label { width: 80px; color: var(--muted); text-align: right; flex-shrink: 0; }
  .chart-bar-track { flex: 1; background: #e5e7eb; border-radius: 4px; height: 22px; position: relative; }
  .chart-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #1a3a5c, #2563eb); display: flex; align-items: center; padding-left: 8px; }
  .chart-bar-fill span { color: white; font-size: 11px; font-weight: 600; }
  .chart-bar-fill.zero { width: 0; }

  @media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .hamburger { display: block; }
    .main { margin-left: 0; }
    .navbar { padding-left: 20px; }
    .form-grid { grid-template-columns: 1fr; }
    .page-content { padding: 16px; }
  }
</style>
</head>
<body>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="<?= BASE_URL ?>/public/images/Logo_ITDA_HD.png" alt="Logo ITDA" style="width:42px;height:42px;object-fit:contain;flex-shrink:0;">
    <span>Arsip Rapat Informatika<small>ITD Adisutjipto</small></span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">
      <div class="nav-section-title">Menu Utama</div>
      <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="nav-item <?= str_starts_with($_GET['url'] ?? '', 'dashboard') ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>
      <a href="<?= BASE_URL ?>/index.php?url=undangan" class="nav-item <?= str_starts_with($_GET['url'] ?? '', 'undangan') ? 'active' : '' ?>">
        <i class="fas fa-envelope-open-text"></i> Undangan Rapat
      </a>
      <a href="<?= BASE_URL ?>/index.php?url=notulensi" class="nav-item <?= str_starts_with($_GET['url'] ?? '', 'notulensi') ? 'active' : '' ?>">
        <i class="fas fa-file-alt"></i> Notulensi Rapat
      </a>
    </div>
  </nav>
  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/index.php?url=auth/logout" class="nav-logout">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
</aside>

<!-- NAVBAR -->
<header class="navbar">
  <div style="display:flex;align-items:center;gap:12px">
    <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
  </div>
  <div class="navbar-user">
    <div class="navbar-user-info">
      <div class="name"><?= htmlspecialchars($_SESSION['user_nama'] ?? '') ?></div>
      <div class="nip">NIP: <?= htmlspecialchars($_SESSION['user_nip'] ?? '') ?></div>
    </div>
    <?php if (!empty($_SESSION['user_foto'])): ?>
      <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($_SESSION['user_foto']) ?>" alt="Foto Profil">
    <?php else: ?>
      <div class="avatar"><?= strtoupper(substr($_SESSION['user_nama'] ?? 'U', 0, 1)) ?></div>
    <?php endif; ?>
  </div>
</header>

<!-- MAIN CONTENT -->
<main class="main">
  <div class="page-content">
    <?php
    // Flash message
    if (isset($_SESSION['flash'])) {
      $flash = $_SESSION['flash'];
      unset($_SESSION['flash']);
      $type = $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'warning' ? 'alert-warning' : 'alert-danger');
      $icon = $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'warning' ? 'exclamation-triangle' : 'times-circle');
      echo "<div class='alert $type'><i class='fas fa-$icon'></i> " . htmlspecialchars($flash['msg']) . "</div>";
    }
    ?>
    <?php require BASE_PATH . '/app/views/' . $content . '.php'; ?>
  </div>
</main>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <h3><i class="fas fa-trash-alt" style="color:var(--danger)"></i> Konfirmasi Hapus</h3>
    <p id="deleteMsg">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeDeleteModal()">Batal</button>
      <form id="deleteForm" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
}
function confirmDelete(url, msg) {
  document.getElementById('deleteForm').action = url;
  if (msg) document.getElementById('deleteMsg').textContent = msg;
  document.getElementById('deleteModal').classList.add('show');
}
function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('show');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeDeleteModal();
});
</script>
</body>
</html>