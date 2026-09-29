<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pembatas akses per role. Pemakaian di route: ['filter' => 'role:admin,sekretaris'].
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session()->get('user');

        if (! $user) {
            return redirect()->to('/login');
        }

        if (! in_array($user['role'] ?? null, $arguments ?? [], true)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
