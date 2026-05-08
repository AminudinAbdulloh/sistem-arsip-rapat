<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function loginPage(): string
    {
        return view('Auth/login', ['title' => 'Login - Arsip Rapat ITD']);
    }

    public function login(): \CodeIgniter\HTTP\RedirectResponse
    {
        $nip = trim($this->request->getPost('nip') ?? '');
        $password = $this->request->getPost('kata_sandi') ?? '';

        if (empty($nip) || empty($password)) {
            return redirect()->to('/login')->with('error', 'NIP dan kata sandi wajib diisi.');
        }

        $user = $this->userModel->findByNip($nip);

        if (!$user || !password_verify($password, $user['kata_sandi'])) {
            return redirect()->to('/login')->with('error', 'NIP atau kata sandi salah.');
        }

        $sessionData = [
            'id' => $user['id'],
            'nip' => $user['nip'],
            'nama' => $user['nama'],
            'jabatan' => $user['jabatan'],
            'foto_profil' => $user['foto_profil']
        ];
        session()->set('user', $sessionData);

        return redirect()->to('/dashboard');
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
