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

    //leads
    $route->get('lead/get', "Leads::get");
    $route->post('lead/add', "Leads::add");
    $route->post('lead/update/(:num)', "Leads::add/$1");
    $route->get('lead/delete/(:num)', "Leads::delete/$1");
    $route->get('lead/undo-delete/(:num)', "Leads::undoDelete/$1");
    $route->get('lead/convert/(:num)', "Customers::convertLeadToCustomer/$1");

    //follow up
    $route->get('follow-up/get/(:num)', "LeadFollowups::get/$1");
    $route->post('follow-up/add', "LeadFollowups::add");
    $route->get('follow-up/delete/(:num)', "LeadFollowups::delete/$1");

    //customer
    $route->get('customer/get', "Customers::get");
    $route->post('customer/add', "Customers::add");
    $route->post('customer/update/(:num)', "Customers::add/$1");
    $route->get('customer/delete/(:num)', "Customers::delete/$1");
    $route->get('customer/undo-delete/(:num)', "Customers::undoDelete/$1");

    //service
    $route->get('customer/service/add/(:num)/(:num)', "Services::add/$1/$2");
    $route->get('customer/service/get/(:num)', "Services::get/$1");
    $route->get('customer/service/get-by-customer-id/(:num)', "Services::getServicesByCustomerId/$1");
});
