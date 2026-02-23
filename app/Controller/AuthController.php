<?php

namespace ArsipRapat\Controller;

use ArsipRapat\App\View;
use ArsipRapat\Model\User;

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function loginPage(): void
    {
        View::render('Auth/login', ['title' => 'Login - Arsip Rapat ITD']);
    }

    public function login(): void
    {
        $nip = trim($_POST['nip'] ?? '');
        $password = $_POST['kata_sandi'] ?? '';

        if (empty($nip) || empty($password)) {
            $_SESSION['error'] = 'NIP dan kata sandi wajib diisi.';
            header('Location: /login');
            exit();
        }

        $user = $this->userModel->findByNip($nip);

        if (!$user || !password_verify($password, $user['kata_sandi'])) {
            $_SESSION['error'] = 'NIP atau kata sandi salah.';
            header('Location: /login');
            exit();
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'nip' => $user['nip'],
            'nama' => $user['nama'],
            'jabatan' => $user['jabatan'],
            'foto_profil' => $user['foto_profil']
        ];

        header('Location: /dashboard');
        exit();
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit();
    }
}
