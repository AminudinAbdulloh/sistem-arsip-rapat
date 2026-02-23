<?php

namespace ArsipRapat\Middleware;

class AuthMiddleware implements Middleware
{
    public function before(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }
    }
}
