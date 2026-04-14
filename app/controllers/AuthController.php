<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/User.php';

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $error = '';

        // ── Rate limiting ──────────────────────────────────────────
        $maxAttempts  = 10;
        $lockDuration = 300; // 5 menit dalam detik

        $attempts  = $_SESSION['login_attempts']   ?? 0;
        $lockedAt  = $_SESSION['login_locked_at']  ?? null;

        // Cek apakah sedang terkunci
        if ($lockedAt !== null) {
            $remaining = $lockDuration - (time() - $lockedAt);
            if ($remaining > 0) {
                $menit  = ceil($remaining / 60);
                $error  = "Terlalu banyak percobaan gagal. Coba lagi dalam {$menit} menit.";
                $this->view('auth/login', ['error' => $error]);
                return; // Hentikan di sini, tidak proses POST
            } else {
                // Waktu kunci sudah habis, reset
                unset($_SESSION['login_attempts'], $_SESSION['login_locked_at']);
                $attempts = 0;
            }
        }
        // ── Akhir rate limiting awal ───────────────────────────────

        if ($this->isPost()) {
            $this->verifyCsrfToken();
            $nip      = $this->trimInput('nip');
            $password = $this->input('password');
            $user     = $this->userModel->findByNip($nip);

            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil — reset counter
                unset($_SESSION['login_attempts'], $_SESSION['login_locked_at']);
                $this->startUserSession($user);
                $this->redirect('dashboard');
            } else {
                // Login gagal — tambah counter
                $_SESSION['login_attempts'] = $attempts + 1;

                if ($_SESSION['login_attempts'] >= $maxAttempts) {
                    $_SESSION['login_locked_at'] = time();
                    $error = "Terlalu banyak percobaan gagal. Akun dikunci selama 5 menit.";
                } else {
                    $sisa  = $maxAttempts - $_SESSION['login_attempts'];
                    $error = "NIP atau kata sandi salah. Sisa percobaan: {$sisa}x.";
                }
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('auth/login');
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private function startUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_nip']  = $user['nip'];
        $_SESSION['user_foto'] = $user['foto_profil'];
    }
}