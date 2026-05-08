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
$routes->get('/dashboard/download', 'DashboardController::downloadLaporan', ['filter' => 'auth']);

// Undangan routes
$routes->get('/undangan', 'UndanganController::index', ['filter' => 'auth']);
$routes->get('/undangan/create', 'UndanganController::create', ['filter' => 'auth']);
$routes->post('/undangan/store', 'UndanganController::store', ['filter' => 'auth']);
$routes->get('/undangan/(:num)/edit', 'UndanganController::edit/$1', ['filter' => 'auth']);
$routes->post('/undangan/(:num)/update', 'UndanganController::update/$1', ['filter' => 'auth']);
$routes->post('/undangan/(:num)/delete', 'UndanganController::delete/$1', ['filter' => 'auth']);
$routes->get('/undangan/(:num)/download', 'UndanganController::downloadPdf/$1', ['filter' => 'auth']);

// Notulensi routes
$routes->get('/notulensi', 'NotulensiController::index', ['filter' => 'auth']);
$routes->get('/notulensi/create', 'NotulensiController::create', ['filter' => 'auth']);
$routes->post('/notulensi/store', 'NotulensiController::store', ['filter' => 'auth']);
$routes->get('/notulensi/(:num)/show', 'NotulensiController::show/$1', ['filter' => 'auth']);
$routes->get('/notulensi/(:num)/edit', 'NotulensiController::edit/$1', ['filter' => 'auth']);
$routes->post('/notulensi/(:num)/update', 'NotulensiController::update/$1', ['filter' => 'auth']);
$routes->post('/notulensi/(:num)/delete', 'NotulensiController::delete/$1', ['filter' => 'auth']);
