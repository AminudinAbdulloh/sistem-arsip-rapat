<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Guest routes (not logged in)
$routes->get('/login', 'AuthController::loginPage', ['filter' => 'guest']);
$routes->post('/login', 'AuthController::login', ['filter' => 'guest']);

// Logout (accessible to all, but only works if logged in)
$routes->get('/logout', 'AuthController::logout');

// Auth required routes
$routes->get('/', 'DashboardController::index', ['filter' => 'auth']);
$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'auth']);
$routes->get('/dashboard/download', 'DashboardController::downloadLaporan', ['filter' => 'role:admin,kaprodi']);

// Undangan routes (baca: admin, sekretaris, kaprodi; tulis: admin, sekretaris)
$routes->get('/undangan', 'UndanganController::index', ['filter' => 'role:admin,sekretaris,kaprodi']);
$routes->get('/undangan/create', 'UndanganController::create', ['filter' => 'role:admin,sekretaris']);
$routes->post('/undangan/store', 'UndanganController::store', ['filter' => 'role:admin,sekretaris']);
$routes->get('/undangan/(:num)/edit', 'UndanganController::edit/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/undangan/(:num)/update', 'UndanganController::update/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/undangan/(:num)/delete', 'UndanganController::delete/$1', ['filter' => 'role:admin,sekretaris']);
$routes->get('/undangan/(:num)/download', 'UndanganController::downloadPdf/$1', ['filter' => 'role:admin,sekretaris']);

// Notulensi routes (baca: admin, sekretaris, kaprodi; tulis: admin, sekretaris)
$routes->get('/notulensi', 'NotulensiController::index', ['filter' => 'role:admin,sekretaris,kaprodi']);
$routes->get('/notulensi/create', 'NotulensiController::create', ['filter' => 'role:admin,sekretaris']);
$routes->post('/notulensi/store', 'NotulensiController::store', ['filter' => 'role:admin,sekretaris']);
$routes->get('/notulensi/(:num)/show', 'NotulensiController::show/$1', ['filter' => 'role:admin,sekretaris,kaprodi']);
$routes->get('/notulensi/(:num)/edit', 'NotulensiController::edit/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/notulensi/(:num)/update', 'NotulensiController::update/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/notulensi/(:num)/delete', 'NotulensiController::delete/$1', ['filter' => 'role:admin,sekretaris']);

// Verifikasi notulensi (khusus kaprodi)
$routes->post('/notulensi/(:num)/verifikasi', 'NotulensiController::verifikasi/$1', ['filter' => 'role:kaprodi']);

// Arsip rapat: hanya notulensi terverifikasi, dapat dilihat semua yang login
$routes->get('/arsip', 'ArsipController::index', ['filter' => 'auth']);
$routes->get('/arsip/(:num)', 'ArsipController::show/$1', ['filter' => 'auth']);

// Manajemen pengguna (khusus admin)
$routes->get('/users', 'UserController::index', ['filter' => 'role:admin']);
$routes->get('/users/create', 'UserController::create', ['filter' => 'role:admin']);
$routes->post('/users/store', 'UserController::store', ['filter' => 'role:admin']);
$routes->get('/users/(:num)/edit', 'UserController::edit/$1', ['filter' => 'role:admin']);
$routes->post('/users/(:num)/update', 'UserController::update/$1', ['filter' => 'role:admin']);
$routes->post('/users/(:num)/delete', 'UserController::delete/$1', ['filter' => 'role:admin']);
