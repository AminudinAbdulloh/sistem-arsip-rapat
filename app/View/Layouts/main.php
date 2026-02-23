<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Arsip Rapat ITD') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #1e3a5f;
            --primary-dark: #152c47;
            --primary-light: #2d5f8f;
            --accent: #c9a227;
            --accent-light: #f0c040;
            --sidebar-w: 260px;
            --navbar-h: 64px;
            --bg: #f0f4f8;
            --white: #ffffff;
            --text: #2d3748;
            --text-muted: #718096;
            --border: #e2e8f0;
            --success: #38a169;
            --danger: #e53e3e;
            --warning: #d69e2e;
            --info: #3182ce;
            --shadow: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w); min-height: 100vh; background: var(--primary);
            position: fixed; left: 0; top: 0; bottom: 0; z-index: 100;
            display: flex; flex-direction: column; transition: transform 0.3s;
            box-shadow: 2px 0 10px rgba(0,0,0,0.15);
        }
        .sidebar-brand {
            padding: 20px 20px 16px; background: var(--primary-dark);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-brand .brand-logo {
            display: flex; align-items: center; gap: 12px; text-decoration: none;
        }
        .brand-icon { width: 42px; height: 42px; background: var(--accent); border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--primary); flex-shrink: 0; }
        .brand-text h2 { color: white; font-size: 13px; font-weight: 700; line-height: 1.2; }
        .brand-text span { color: rgba(255,255,255,0.6); font-size: 11px; }
        .sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; }
        .nav-label { padding: 8px 20px 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: rgba(255,255,255,0.4); }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 20px;
            color: rgba(255,255,255,0.75); text-decoration: none; font-size: 14px; font-weight: 500;
            transition: all 0.2s; position: relative; margin: 2px 0; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: var(--accent); color: var(--primary); font-weight: 700; }
        .nav-item.active::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: var(--accent-light); }
        .nav-item i { width: 18px; text-align: center; font-size: 15px; }
        .nav-item.logout { color: #fc8181; margin-top: 8px; }
        .nav-item.logout:hover { background: rgba(252,129,129,0.15); color: #ff6b6b; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-footer p { color: rgba(255,255,255,0.4); font-size: 11px; text-align: center; }

        /* Navbar */
        .navbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0; height: var(--navbar-h);
            background: var(--white); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px; z-index: 90; box-shadow: var(--shadow);
        }
        .navbar-left h1 { font-size: 18px; font-weight: 700; color: var(--primary); }
        .navbar-left p { font-size: 12px; color: var(--text-muted); }
        .navbar-right { display: flex; align-items: center; gap: 16px; }
        .user-profile { display: flex; align-items: center; gap: 10px; }
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent); }
        .user-avatar-placeholder { width: 38px; height: 38px; border-radius: 50%; background: var(--primary);
            display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 15px;
            border: 2px solid var(--accent); }
        .user-info { text-align: right; }
        .user-info .name { font-size: 13px; font-weight: 600; color: var(--text); }
        .user-info .role { font-size: 11px; color: var(--text-muted); }

        /* Main content */
        .main-content { margin-left: var(--sidebar-w); margin-top: var(--navbar-h); flex: 1; padding: 28px; min-height: calc(100vh - var(--navbar-h)); }

        /* Cards */
        .card { background: var(--white); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; }
        .card-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-header h2 { font-size: 16px; font-weight: 700; color: var(--primary); }
        .card-body { padding: 24px; }

        /* Stat cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .stat-card { background: var(--white); border-radius: 12px; padding: 22px; box-shadow: var(--shadow);
            display: flex; align-items: center; gap: 16px; position: relative; overflow: hidden; }
        .stat-card::after { content: ''; position: absolute; right: -10px; top: -10px; width: 80px; height: 80px;
            border-radius: 50%; opacity: 0.08; }
        .stat-card.blue::after { background: var(--info); }
        .stat-card.green::after { background: var(--success); }
        .stat-card.orange::after { background: var(--warning); }
        .stat-card.purple::after { background: #805ad5; }
        .stat-icon { width: 54px; height: 54px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .stat-icon.blue { background: #ebf8ff; color: var(--info); }
        .stat-icon.green { background: #f0fff4; color: var(--success); }
        .stat-icon.orange { background: #fffaf0; color: var(--warning); }
        .stat-icon.purple { background: #faf5ff; color: #805ad5; }
        .stat-info .value { font-size: 28px; font-weight: 800; color: var(--text); line-height: 1; }
        .stat-info .label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

        /* Table */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        thead th { background: #f7fafc; padding: 12px 16px; text-align: left; font-weight: 700;
            font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);
            border-bottom: 2px solid var(--border); white-space: nowrap; }
        tbody td { padding: 14px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:hover { background: #f7fafc; }
        tbody tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .badge-blue { background: #ebf8ff; color: var(--info); }
        .badge-green { background: #f0fff4; color: var(--success); }
        .badge-orange { background: #fffaf0; color: var(--warning); }
        .badge-red { background: #fff5f5; color: var(--danger); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-accent { background: var(--accent); color: var(--primary); }
        .btn-accent:hover { background: var(--accent-light); }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-info { background: var(--info); color: white; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-outline { background: transparent; border: 2px solid var(--primary); color: var(--primary); }
        .btn-outline:hover { background: var(--primary); color: white; }

        /* Forms */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border: 2px solid var(--border); border-radius: 8px;
            font-size: 14px; color: var(--text); transition: border-color 0.2s; background: white; outline: none; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,58,95,0.1); }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-actions { display: flex; gap: 12px; margin-top: 24px; }
        .required { color: var(--danger); }

        /* Alerts */
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .alert-success { background: #f0fff4; color: #276749; border: 1px solid #c6f6d5; }
        .alert-danger { background: #fff5f5; color: #9b2c2c; border: 1px solid #fed7d7; }
        .alert-warning { background: #fffaf0; color: #7b341e; border: 1px solid #fbd38d; }

        /* Page header */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 800; color: var(--primary); }
        .page-header p { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        .breadcrumb a { color: var(--primary); text-decoration: none; }

        /* Action buttons in table */
        .action-btns { display: flex; gap: 6px; }

        /* Image thumbnail */
        .img-thumb { width: 50px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); }

        /* Empty state */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 60px; color: #e2e8f0; margin-bottom: 16px; }
        .empty-state h3 { color: var(--text-muted); font-size: 18px; margin-bottom: 8px; }
        .empty-state p { color: var(--text-muted); font-size: 14px; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .navbar { left: 0; }
            .main-content { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="/dashboard" class="brand-logo">
            <div class="brand-icon"><i class="fas fa-archive"></i></div>
            <div class="brand-text">
                <h2>Arsip Rapat ITD</h2>
                <span>Adisutjipto</span>
            </div>
        </a>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>
        <a href="/dashboard" class="nav-item <?= strpos($_SERVER['PATH_INFO'] ?? '/', '/dashboard') === 0 ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="/undangan" class="nav-item <?= strpos($_SERVER['PATH_INFO'] ?? '/', '/undangan') === 0 ? 'active' : '' ?>">
            <i class="fas fa-envelope-open-text"></i> Undangan Rapat
        </a>
        <a href="/notulensi" class="nav-item <?= strpos($_SERVER['PATH_INFO'] ?? '/', '/notulensi') === 0 ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> Notulensi Rapat
        </a>
        <div class="nav-label" style="margin-top:12px">Akun</div>
        <a href="/logout" class="nav-item logout" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
    <div class="sidebar-footer">
        <p>&copy; <?= date('Y') ?> Program Studi ITD Adisutjipto</p>
    </div>
</aside>

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-left">
        <h1><?= htmlspecialchars($navTitle ?? ($title ?? 'Arsip Rapat')) ?></h1>
    </div>
    <div class="navbar-right">
        <div class="user-profile">
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($_SESSION['user']['nama'] ?? '') ?></div>
                <div class="role"><?= htmlspecialchars($_SESSION['user']['jabatan'] ?? 'Staff') ?></div>
            </div>
            <?php if (!empty($_SESSION['user']['foto_profil'])): ?>
                <img src="/uploads/dokumentasi/<?= htmlspecialchars($_SESSION['user']['foto_profil']) ?>" alt="Profil" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder">
                    <?= strtoupper(substr($_SESSION['user']['nama'] ?? 'U', 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php $content(); ?>
</main>
</body>
</html>
