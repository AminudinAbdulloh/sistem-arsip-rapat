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

        if ($this->isPost()) {
            $nip      = $this->trimInput('nip');
            $password = $this->input('password');
            $user     = $this->userModel->findByNip($nip);

            if ($user && password_verify($password, $user['password'])) {
                $this->startUserSession($user);
                $this->redirect('dashboard');
            } else {
                $error = 'NIP atau kata sandi salah.';
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
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_nip']  = $user['nip'];
        $_SESSION['user_foto'] = $user['foto_profil'];
    }
}