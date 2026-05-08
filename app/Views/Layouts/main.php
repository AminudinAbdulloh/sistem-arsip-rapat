<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Arsip Rapat ITD') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { transition: all 0.3s ease; }
        .nav-item:hover { background: rgba(255,255,255,0.1); }
        .card-hover { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="sidebar w-64 bg-gradient-to-b from-[#1e3a5f] to-[#2d5f8f] text-white flex flex-col">
            <div class="p-6 border-b border-white/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center">
                        <img src="<?= base_url('favicon.ico') ?>" alt="Logo" class="w-10 h-10 object-contain drop-shadow-md">
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-tight">Arsip Rapat</h1>
                        <p class="text-xs text-white/70">ITD Adisutjipto</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 py-4">
                <a href="/dashboard" class="nav-item flex items-center gap-3 px-6 py-3 <?= url_is('dashboard*') ? 'bg-white/20 border-r-4 border-white' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/undangan" class="nav-item flex items-center gap-3 px-6 py-3 <?= url_is('undangan*') ? 'bg-white/20 border-r-4 border-white' : '' ?>">
                    <i class="fas fa-envelope w-5"></i>
                    <span>Undangan Rapat</span>
                </a>
                <a href="/notulensi" class="nav-item flex items-center gap-3 px-6 py-3 <?= url_is('notulensi*') ? 'bg-white/20 border-r-4 border-white' : '' ?>">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Notulensi</span>
                </a>
            </nav>

            <div class="p-4 border-t border-white/20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center overflow-hidden">
                        <?php if (!empty(session()->get('user')['foto_profil'])): ?>
                            <img src="/uploads/profil/<?= session()->get('user')['foto_profil'] ?>" alt="Profile" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-user text-sm"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate"><?= esc(session()->get('user')['nama'] ?? 'User') ?></p>
                        <p class="text-xs text-white/70 truncate"><?= esc(session()->get('user')['jabatan'] ?? '') ?></p>
                    </div>
                </div>
                <a href="/logout" class="mt-3 flex items-center gap-2 text-sm text-white/80 hover:text-white">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800"><?= esc($title ?? 'Dashboard') ?></h2>
                    <div class="text-sm text-gray-500">
                        <?= date('l, d F Y') ?>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="mx-8 mt-4 bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded">
                    <p class="font-medium"><i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?></p>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mx-8 mt-4 bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded">
                    <p class="font-medium"><i class="fas fa-exclamation-circle mr-2"></i><?= session()->getFlashdata('error') ?></p>
                </div>
            <?php endif; ?>

            <!-- Content -->
            <div class="p-8">
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>
</body>
</html>
