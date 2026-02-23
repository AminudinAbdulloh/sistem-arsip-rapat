<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use ArsipRapat\App\Router;
use ArsipRapat\Controller\AuthController;
use ArsipRapat\Controller\DashboardController;
use ArsipRapat\Controller\UndanganController;
use ArsipRapat\Controller\NotulensiController;
use ArsipRapat\Middleware\AuthMiddleware;
use ArsipRapat\Middleware\GuestMiddleware;

// Auth routes
Router::add('GET', '/login', AuthController::class, 'loginPage', [GuestMiddleware::class]);
Router::add('POST', '/login', AuthController::class, 'login');
Router::add('GET', '/logout', AuthController::class, 'logout');

// Dashboard
Router::add('GET', '/', DashboardController::class, 'index', [AuthMiddleware::class]);
Router::add('GET', '/dashboard', DashboardController::class, 'index', [AuthMiddleware::class]);
Router::add('GET', '/dashboard/download', DashboardController::class, 'downloadLaporan', [AuthMiddleware::class]);

// Undangan Rapat
Router::add('GET', '/undangan', UndanganController::class, 'index', [AuthMiddleware::class]);
Router::add('GET', '/undangan/create', UndanganController::class, 'create', [AuthMiddleware::class]);
Router::add('POST', '/undangan/store', UndanganController::class, 'store', [AuthMiddleware::class]);
Router::add('GET', '/undangan/([0-9]+)/edit', UndanganController::class, 'edit', [AuthMiddleware::class]);
Router::add('POST', '/undangan/([0-9]+)/update', UndanganController::class, 'update', [AuthMiddleware::class]);
Router::add('POST', '/undangan/([0-9]+)/delete', UndanganController::class, 'delete', [AuthMiddleware::class]);
Router::add('GET', '/undangan/([0-9]+)/download', UndanganController::class, 'downloadPdf', [AuthMiddleware::class]);

// Notulensi Rapat
Router::add('GET', '/notulensi', NotulensiController::class, 'index', [AuthMiddleware::class]);
Router::add('GET', '/notulensi/create', NotulensiController::class, 'create', [AuthMiddleware::class]);
Router::add('POST', '/notulensi/store', NotulensiController::class, 'store', [AuthMiddleware::class]);
Router::add('GET', '/notulensi/([0-9]+)/show', NotulensiController::class, 'show', [AuthMiddleware::class]);
Router::add('GET', '/notulensi/([0-9]+)/edit', NotulensiController::class, 'edit', [AuthMiddleware::class]);
Router::add('POST', '/notulensi/([0-9]+)/update', NotulensiController::class, 'update', [AuthMiddleware::class]);
Router::add('POST', '/notulensi/([0-9]+)/delete', NotulensiController::class, 'delete', [AuthMiddleware::class]);

Router::run();
