<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/test', function () {
    echo "Service Is Up";
    exit();
});
// Authentication Routes...
$routes->post('/login', "Login::loginCheck");
$routes->post('/otp/send', "Otps::send");
$routes->post('/otp/verify', "Otps::verify");
$routes->get('cities/get', 'Cities::getAll');
$routes->get('education/get', 'Educations::getAll');

$routes->group('/admin', ['filter' => 'adminAuth'], static function ($route) {
    $route->get('dashboard', "Admin\Dashboard::index");
    $route->get('employee/get', "Employees::get");
    $route->post('employee/add', "Employees::add");
    $route->post('employee/update/(:num)', "Employees::add/$1");
    $route->get('employee/delete/(:num)', "Employees::delete/$1");
    $route->get('employee/undo-delete/(:num)', "Employees::undoDelete/$1");
});
