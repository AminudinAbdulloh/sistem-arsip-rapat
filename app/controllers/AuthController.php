<?php
require_once BASE_PATH . '/app/controllers/Controller.php';
require_once BASE_PATH . '/app/models/User.php';

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login($param = null) {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nip = trim($_POST['nip'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByNip($nip);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_nip'] = $user['nip'];
                $_SESSION['user_foto'] = $user['foto_profil'];
                $this->redirect('dashboard');
            } else {
                $error = 'NIP atau kata sandi salah.';
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    public function logout($param = null) {
        session_destroy();
        $this->redirect('auth/login');
    }
}