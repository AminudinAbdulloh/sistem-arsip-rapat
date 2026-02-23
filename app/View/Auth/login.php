<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #1e3a5f; --primary-dark: #152c47; --accent: #c9a227;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2d5f8f 50%, #1e3a5f 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .login-container {
            width: 100%; max-width: 440px; padding: 20px;
            position: relative; z-index: 1;
        }
        .login-card {
            background: white; border-radius: 20px; padding: 48px 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center; margin-bottom: 32px;
        }
        .logo-icon {
            width: 72px; height: 72px; background: linear-gradient(135deg, var(--primary), #2d5f8f);
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 32px; color: white; box-shadow: 0 8px 24px rgba(30,58,95,0.3);
        }
        .login-logo h1 { font-size: 22px; font-weight: 800; color: var(--primary); margin-bottom: 4px; }
        .login-logo p { font-size: 13px; color: #718096; }
        .divider { height: 1px; background: #e2e8f0; margin: 28px 0; position: relative; }
        .divider span { position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
            background: white; padding: 0 12px; font-size: 12px; color: #a0aec0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #2d3748; margin-bottom: 8px; }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #a0aec0; font-size: 15px; }
        .form-control {
            width: 100%; padding: 12px 14px 12px 42px; border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; color: #2d3748; transition: all 0.2s; outline: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,58,95,0.1); }
        .toggle-password { position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #a0aec0; font-size: 14px; background: none; border: none; padding: 0; }
        .btn-login {
            width: 100%; padding: 13px; background: linear-gradient(135deg, var(--primary), #2d5f8f);
            color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: all 0.2s; margin-top: 8px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(30,58,95,0.35); }
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 13px; }
        .alert-danger { background: #fff5f5; color: #c53030; border: 1px solid #fed7d7; }
        .footer-note { text-align: center; margin-top: 24px; font-size: 12px; color: #a0aec0; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-icon"><i class="fas fa-archive"></i></div>
            <h1>Arsip Rapat ITD</h1>
            <p>Program Studi ITD Adisutjipto</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="divider"><span>Masuk ke Sistem</span></div>

        <form method="POST" action="/login">
            <div class="form-group">
                <label for="nip">Nomor Induk Pegawai (NIP)</label>
                <div class="input-wrapper">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="nip" name="nip" class="form-control"
                           placeholder="Masukkan NIP Anda" required autocomplete="username"
                           value="<?= htmlspecialchars($_POST['nip'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="kata_sandi">Kata Sandi</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="kata_sandi" name="kata_sandi" class="form-control"
                           placeholder="Masukkan kata sandi" required autocomplete="current-password">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="footer-note">
            Sistem Informasi Pengelolaan Arsip Rapat<br>
            &copy; <?= date('Y') ?> ITD Adisutjipto
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('kata_sandi');
    const icon = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>
