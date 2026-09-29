<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class UserController extends BaseController
{
    protected UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function index(): string
    {
        return view('Users/index', [
            'title' => 'Kelola Pengguna - Arsip ITD',
            'users' => $this->model->orderBy('nama', 'ASC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('Users/create', ['title' => 'Tambah Pengguna']);
    }

    public function store(): RedirectResponse
    {
        $data = $this->collectInput();

        if (! $this->validateData($data, $this->model->rulesFor(null, true))) {
            return redirect()->to('/users/create')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->model->insert([
            'nip'        => $data['nip'],
            'nama'       => $data['nama'],
            'jabatan'    => $data['jabatan'],
            'role'       => $data['role'],
            'kata_sandi' => password_hash($data['kata_sandi'], PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('Users/edit', ['title' => 'Edit Pengguna', 'user' => $user]);
    }

    public function update(int $id): RedirectResponse
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        $data = $this->collectInput();

        if (! $this->validateData($data, $this->model->rulesFor($id, false))) {
            return redirect()->to("/users/{$id}/edit")->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        if ($user['role'] !== $data['role']) {
            if ($id === $this->currentUserId()) {
                return redirect()->to("/users/{$id}/edit")->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
            }
            if ($user['role'] === 'admin' && $this->model->countByRole('admin') <= 1) {
                return redirect()->to("/users/{$id}/edit")->with('error', 'Admin terakhir tidak dapat diturunkan rolenya.');
            }
        }

        $update = [
            'nip'     => $data['nip'],
            'nama'    => $data['nama'],
            'jabatan' => $data['jabatan'],
            'role'    => $data['role'],
        ];
        if ($data['kata_sandi'] !== '') {
            $update['kata_sandi'] = password_hash($data['kata_sandi'], PASSWORD_DEFAULT);
        }
        $this->model->update($id, $update);

        if ($id === $this->currentUserId()) {
            session()->set('user', array_merge(session()->get('user'), [
                'nip' => $data['nip'], 'nama' => $data['nama'], 'jabatan' => $data['jabatan'],
            ]));
        }

        return redirect()->to('/users')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        if ($id === $this->currentUserId()) {
            return redirect()->to('/users')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        if ($user['role'] === 'admin' && $this->model->countByRole('admin') <= 1) {
            return redirect()->to('/users')->with('error', 'Admin terakhir tidak dapat dihapus.');
        }
        // FK created_by memakai ON DELETE CASCADE; tanpa pengecekan ini undangan/notulensi ikut terhapus.
        if ($this->model->hasRelatedData($id)) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak dapat dihapus karena sudah memiliki data undangan/notulensi.');
        }

        $this->model->delete($id);

        return redirect()->to('/users')->with('success', 'Pengguna berhasil dihapus.');
    }

    private function currentUserId(): int
    {
        return (int) (session()->get('user')['id'] ?? 0);
    }

    private function collectInput(): array
    {
        return [
            'nip'        => trim($this->request->getPost('nip') ?? ''),
            'nama'       => trim($this->request->getPost('nama') ?? ''),
            'jabatan'    => trim($this->request->getPost('jabatan') ?? ''),
            'role'       => $this->request->getPost('role') ?? '',
            'kata_sandi' => $this->request->getPost('kata_sandi') ?? '',
        ];
    }
}
