<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .login-bg {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5f8f 50%, #3d7abf 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
    <div class="glass-card w-full max-w-md rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="w-24 h-24 flex items-center justify-center mx-auto mb-4">
                <img src="<?= base_url('favicon.ico') ?>" alt="Logo ITD" class="w-24 h-24 object-contain drop-shadow-lg">
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Selamat Datang</h1>
            <p class="text-gray-500 text-lg mt-1">Sistem Informasi Arsip Rapat</p>
            <p class="text-[#1e3a5f] font-semibold text-lg">ITD Adisutjipto</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-6">
                <p class="text-sm"><i class="fas fa-exclamation-circle mr-2"></i><?= session()->getFlashdata('error') ?></p>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-400">
                        <i class="fas fa-id-card"></i>
                    </span>
                    <input type="text" name="nip" required
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent outline-none transition-all"
                        placeholder="Masukkan NIP">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="kata_sandi" required
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#1e3a5f] focus:border-transparent outline-none transition-all"
                        placeholder="Masukkan kata sandi">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-[#1e3a5f] to-[#2d5f8f] text-white font-semibold py-2.5 rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
                <i class="fas fa-sign-in-alt mr-2"></i>Masuk
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">
                Default: NIP <code class="bg-gray-100 px-1 rounded">198001012005011001</code> | Password: <code class="bg-gray-100 px-1 rounded">password</code>
            </p>
        </div>
    </div>
</body>
</html>
