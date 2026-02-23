<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Composer autoloader (untuk Dompdf dan library lain)
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// Auto-generate a real bcrypt hash for 'admin123'
// The default user uses 'password' as hash placeholder; actual password is set below

define('BASE_PATH', __DIR__);
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));

// Simple router
$url = isset($_GET['url']) ? trim($_GET['url'], '/') : 'dashboard';
$segments = explode('/', $url);

$controllerName = ucfirst($segments[0] ?? 'dashboard') . 'Controller';
$action = $segments[1] ?? 'index';
$param = $segments[2] ?? null;

$controllerFile = BASE_PATH . '/app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    if (method_exists($controller, $action)) {
        $controller->$action($param);
    } else {
        http_response_code(404);
        echo "Method tidak ditemukan.";
    }
} else {
    // Default to dashboard
    require_once BASE_PATH . '/app/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
}