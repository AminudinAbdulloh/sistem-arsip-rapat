<?php

/**
 * Base Controller
 * Menyediakan helper umum untuk semua controller turunan.
 */
class Controller
{
    // ----------------------------------------------------------------
    // View & Redirect
    // ----------------------------------------------------------------

    protected function view(string $viewPath, array $data = []): void
    {
        extract($data);
        $file = BASE_PATH . '/app/views/' . $viewPath . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            http_response_code(404);
            die("View tidak ditemukan: {$viewPath}");
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/index.php?url=' . ltrim($path, '/'));
        exit;
    }

    // ----------------------------------------------------------------
    // Auth helpers
    // ----------------------------------------------------------------

    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    // ----------------------------------------------------------------
    // Flash messages
    // ----------------------------------------------------------------

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
    }

    protected function flashSuccess(string $message): void
    {
        $this->flash('success', $message);
    }

    protected function flashWarning(string $message): void
    {
        $this->flash('warning', $message);
    }

    protected function flashError(string $message): void
    {
        $this->flash('error', $message);
    }

    // ----------------------------------------------------------------
    // Layout helper — render halaman utama
    // ----------------------------------------------------------------

    /**
     * Shortcut untuk merender layout utama dengan konten dinamis.
     *
     * @param string $title    Judul halaman
     * @param string $content  Path view konten relatif terhadap app/views/
     * @param array  $data     Data tambahan yang di-extract ke view
     */
    protected function renderMain(string $title, string $content, array $data = []): void
    {
        $this->view('layouts/main', array_merge($data, [
            'title'   => $title,
            'content' => $content,
        ]));
    }

    // ----------------------------------------------------------------
    // Request helpers
    // ----------------------------------------------------------------

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function input(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function trimInput(string $key, mixed $default = ''): string
    {
        return trim($this->input($key, $default));
    }
}