<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

$sessionLifetime = 3600; // 1 jam
if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > $sessionLifetime) {
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php?url=auth/login');
    exit;
}
$_SESSION['last_active'] = time();

require_once __DIR__ . '/config/database.php';

// Composer autoloader (opsional — untuk library tambahan)
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// Konstanta global
define('BASE_PATH', __DIR__);
define('BASE_URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')
);

// Autoload helpers
foreach (glob(BASE_PATH . '/app/helpers/*.php') as $helperFile) {
    require_once $helperFile;
}

// ----------------------------------------------------------------
// Router sederhana: ?url=controller/action/param
// ----------------------------------------------------------------
$url      = trim($_GET['url'] ?? 'dashboard', '/');
$segments = explode('/', $url);

$controllerName = ucfirst($segments[0]) . 'Controller';
$action         = $segments[1] ?? 'index';
$param          = $segments[2] ?? null;

$controllerFile = BASE_PATH . '/app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();

    if (method_exists($controller, $action)) {
        $controller->$action($param);
    } else {
        http_response_code(404);
        echo "Action tidak ditemukan: {$action}";
    }
} else {
    // Fallback ke dashboard
    require_once BASE_PATH . '/app/controllers/DashboardController.php';
    (new DashboardController())->index();
}