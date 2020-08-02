<?php

namespace FAPI\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// $routes->get( 'fapi', '\FAPI\Controllers\Home::index' );

#GROUP
$routes->group('fapi', ['namespace' => '\FAPI\Controllers'], function($routes) {

	$routes->get( '/', 'Home::index' );

	# === POST ===
	$routes->group('post', ['namespace' => '\FAPI\Controllers\Post'], function($routes) {
  	$routes->get('list', 'Article');
	});

	# === PAGE ===
  $routes->group('page', ['namespace' => '\FAPI\Controllers\Page'], function($routes) {
  	$routes->get('/', 'Tree::index');
  	$routes->get('list', 'Tree::index');
  });

});