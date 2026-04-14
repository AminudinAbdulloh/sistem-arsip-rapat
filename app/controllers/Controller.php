<?php
class Controller {
    protected function view($viewPath, $data = []) {
        extract($data);
        $file = BASE_PATH . '/app/views/' . $viewPath . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("View tidak ditemukan: $viewPath");
        }
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . '/index.php?url=' . $url);
        exit;
    }

    protected function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}