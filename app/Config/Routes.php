<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// =========================================================
// Public Routes
// =========================================================
$routes->get('/', 'Home::index');

// ⚠️  PLACEHOLDER — AuthController does not exist in this kit yet.
//     This route will throw PageNotFoundException if hit in production.
//     Remove this line or create app/Controllers/AuthController.php before deploying.
//     See Shield documentation for login implementation: https://shield.codeigniter.com
$routes->post('login', 'AuthController::login');

// =========================================================
// API Routes — Public (no auth required)
// =========================================================
$routes->get('api/ping', 'Api\PingController::index');

// =========================================================
// API Routes — Protected (apiKeyFilter)
// =========================================================
$routes->group('api', ['filter' => 'apiKeyFilter'], static function (RouteCollection $routes): void {
    // Health check (authenticated)
    $routes->get('protected', 'Api\PingController::check');

    // User resource (CRUD)
    $routes->get('users', 'Api\UserController::index');
    $routes->post('users', 'Api\UserController::create');
    $routes->get('users/(:num)', 'Api\UserController::show/$1');
    $routes->put('users/(:num)', 'Api\UserController::update/$1');
    $routes->delete('users/(:num)', 'Api\UserController::delete/$1');
});

// =========================================================
// Web Routes — Protected (authFilter)
// =========================================================
// ⚠️  PLACEHOLDER — DashboardController does not exist in this kit yet.
//     This route will throw PageNotFoundException if hit in production.
//     Remove this line or create app/Controllers/DashboardController.php before deploying.
$routes->group('', ['filter' => 'authFilter'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'DashboardController::index');
});

// Shield auth routes (login, register, magic-link, etc.)
service('auth')->routes($routes);
