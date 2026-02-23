<?php

namespace ArsipRapat\Middleware;

class GuestMiddleware implements Middleware
{
    public function before(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit();
        }
    }
}
