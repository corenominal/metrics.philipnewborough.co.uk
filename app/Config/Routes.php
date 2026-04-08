<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Admin routes
$routes->get('/admin', 'Admin\Home::index');
$routes->get('/admin/datatable', 'Admin\Home::datatable');
$routes->post('/admin/delete', 'Admin\Home::delete');
$routes->get('/admin/metrics', 'Admin\Metrics::index');
$routes->get('/admin/metrics/domains', 'Admin\Metrics::domains');
$routes->get('/admin/metrics/domain/(:any)', 'Admin\Metrics::domain/$1');
$routes->post('/admin/metrics/domain/(:any)/delete', 'Admin\Metrics::deleteDomain/$1');
$routes->get('/admin/reset', 'Admin\Reset::index');
$routes->post('/admin/reset', 'Admin\Reset::reset');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping', ['filter' => 'apifilter']);
$routes->match(['post', 'options'], '/api/metrics/receive', 'Api\Metrics::receive', ['filter' => 'apifilter']);
$routes->match(['post', 'options'], '/api/metrics/receivepwa', 'Api\Metrics::receivePwa', ['filter' => 'optionalapifilter']);

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');

// Logout route
$routes->get('/logout', 'Auth::logout');

// Unauthorised route
$routes->get('/unauthorised', 'Unauthorised::index');

// Custom 404 route
$routes->set404Override('App\Controllers\Errors::show404');

// Debug routes
$routes->get('/debug', 'Debug\Home::index');
$routes->get('/debug/(:segment)', 'Debug\Rerouter::reroute/$1');
$routes->get('/debug/(:segment)/(:segment)', 'Debug\Rerouter::reroute/$1/$2');
