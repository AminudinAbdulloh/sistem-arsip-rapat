<?php

if (! function_exists('has_role')) {
    /**
     * True bila role pengguna yang login termasuk salah satu $roles.
     * Hanya untuk menyesuaikan tampilan; penjagaan akses ada di RoleFilter.
     */
    function has_role(string ...$roles): bool
    {
        $role = session()->get('user')['role'] ?? null;

        return $role !== null && in_array($role, $roles, true);
    }
}
