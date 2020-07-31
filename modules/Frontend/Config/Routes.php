<?php
namespace FAPI\Config;

#GROUP
$routes->group('fapi', ['namespace' => 'FAPI\Controllers'], function($routes) {

	$routes->get('/', 'Home::index');
	#GROUP
	$routes->group('post', ['namespace' => '\FAPI\Controllers\Post'], function($routes) {
  	$routes->get('list', 'Article');
  });
  $routes->group('page', ['namespace' => '\FAPI\Controllers\Page'], function($routes) {
  	$routes->get('list', 'Tree');
  });

});