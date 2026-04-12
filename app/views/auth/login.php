<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Arsip Rapat ITD Adisutjipto</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  :root {
    --primary: #1a3a5c;
    --primary-light: #2563eb;
    --accent: #f59e0b;
    --bg: #f0f4f8;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #1a3a5c 0%, #2563eb 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .login-wrapper {
    display: flex;
    width: 900px;
    max-width: 98vw;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
  }
  .login-left {
    flex: 1;
    background: linear-gradient(160deg, #1a3a5c 0%, #0f2644 100%);
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
  }
  .login-left .logo-icon {
    width: 80px; height: 80px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 36px;
    margin-bottom: 20px;
    border: 2px solid rgba(255,255,255,0.3);
  }
  .login-left h2 { font-size: 22px; margin-bottom: 10px; }
  .login-left p { font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.6; }
  .login-left .badge {
    margin-top: 24px;
    background: rgba(245,158,11,0.2);
    border: 1px solid #f59e0b;
    color: #fcd34d;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 12px;
  }
  .login-right {
    flex: 1;
    background: white;
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .login-right h3 { font-size: 24px; color: var(--primary); margin-bottom: 6px; }
  .login-right p.sub { color: #6b7280; font-size: 14px; margin-bottom: 30px; }
  .form-group { margin-bottom: 20px; }
  label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .input-wrap { position: relative; }
  .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
  input[type=text], input[type=password] {
    width: 100%; padding: 12px 14px 12px 40px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color .2s;
    outline: none;
  }
  input:focus { border-color: var(--primary-light); }
  .btn-login {
    width: 100%;
    padding: 13px;
    background: linear-gradient(90deg, #1a3a5c, #2563eb);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s;
    margin-top: 8px;
  }
  .btn-login:hover { opacity: .9; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
  .alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
  .info-default {
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;
    padding: 12px; font-size: 12px; color: #1d4ed8; margin-top: 20px;
  }
  @media (max-width: 640px) {
    .login-left { display: none; }
    .login-right { padding: 30px 24px; }
  }
</style>
</head>
<body>
<div class="login-wrapper">
  <div class="login-left">
  <img src="<?= BASE_URL ?>/public/images/Logo_ITDA_HD.png" alt="Logo ITDA" style="width:110px;height:110px;object-fit:contain;margin-bottom:20px;">
    <h2>Sistem Informasi Pengelolaan Arsip Rapat</h2>
    <p>Institut Teknologi Dirgantara Adisutjipto<br>Program Studi</p>
  </div>
  <div class="login-right">
    <h3>Selamat Datang</h3>
    <p class="sub">Masuk dengan NIP dan kata sandi Anda</p>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>NIP</label>
        <div class="input-wrap">
          <i class="fas fa-id-card"></i>
          <input type="text" name="nip" placeholder="Masukkan NIP Anda" required>
        </div>
      </div>
      <div class="form-group">
        <label>Kata Sandi</label>
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" placeholder="Masukkan kata sandi" required>
        </div>
      </div>
      <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Masuk</button>
    </form>
  </div>
</div>
</body>
</html>