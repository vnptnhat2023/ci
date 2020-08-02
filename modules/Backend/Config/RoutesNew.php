<?php

namespace BAPI\Config;

/**
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

# Option array[ controller, placeholder, only, except, websafe ]
# only & except: ['index', 'show', 'create', 'update', 'delete', 'new', 'edit']

$bapiPermData = [
	'user',
	'post',
	'page',
	'category',
	'general_group',
	'general_item',
	'general_relation',
	'general_theme',
	'extension'
];

$bapiPermStr = implode( ',', $bapiPermData );

$bapiOptions = [
	'namespace' => '\BAPI\Controllers',
	'filter' => "NknAuth:{$bapiPermStr}"
];

$routeData = [

	[
		'user',
		[
			'namespace' => '\BAPI\Controllers\User',
			'filter' => 'NknAuth:all'
		],
		function( $routes )
		{
			# ___ Crud ___
			$routes->get( '/', 'Crud::index');
			$routes->get( 'crud/(:num)/edit', 'Crud::edit/$1' );
			$routes->get( 'crud/(:num)', 'Crud::show/$1' );
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:dotID)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );

			# ___ Group ___
			$routes->resource( 'group', [
				'placeholder' => '(:num)',
				'only' => [ 'index', 'create', 'delete', 'update' ]
			] );

			# ___ Setting ___
			# $routes->get( 'setting|setting(:segment)', 'Setting::index/$1' );
			$routes->resource( 'general_setting', [
				'placeholder' => '(:dashAlpha)',
				'only' => [ 'index', 'create', 'update', 'delete' ]
			] );
		}
	],

	[
		'post',
		[
			'namespace' => '\BAPI\Controllers\Post',
			'filter' => 'NknAuth:post'
		],
		function( $routes )
		{
			# ___ Crud ___
			$routes->get( '/', 'Crud::index');
			$routes->get( 'crud/(:num)/edit', 'Crud::edit/$1' );
			$routes->get( 'crud/(:num)', 'Crud::show/$1' );
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:dotID)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );

			# ___ SETTING ___
			$routes->resource( 'setting', [
				'placeholder' => '(:dashAlpha)',
				'only' => [ 'index', 'create', 'update', 'delete' ]
			] );
		}
	],

	[
		'page',
		[
			'namespace' => '\BAPI\Controllers\Page',
			'filter' => 'NknAuth:page'
		],
		function( $routes )
		{
			$routes->get( '/', 'Crud::index');
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:num)', 'Crud::update/$1' );
		}
	],

	[
		'category',
		[
			'namespace' => '\BAPI\Controllers\Category',
			'filter' => 'NknAuth:category'
		],
		function( $routes )
		{
			$routes->get( '/', 'Crud::index');
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	],

	[
		'general_group',
		[
			'namespace' => '\BAPI\Controllers\GeneralGroup',
			'filter' => 'NknAuth:general_group'
		],
		function( $routes )
		{
			// $routes->get( '/', 'Crud::index');
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	],

	[
		'general_item',
		[
			'namespace' => '\BAPI\Controllers\GeneralItem',
			'filter' => 'NknAuth:general_item'
		],
		function( $routes )
		{
			// $routes->get( '/', 'Crud::index');
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	],

	[
		'general_relation',
		[
			'namespace' => '\BAPI\Controllers\GeneralRelation',
			'filter' => 'NknAuth:general_relation'
		],
		function( $routes )
		{
			$routes->get( '/', 'GroupItem::index');
		}
	],

	[
		'general_theme',
		[
			'namespace' => '\BAPI\Controllers\Theme',
			'filter' => 'NknAuth:general_theme'
		],
		function( $routes )
		{
			$routes->get( '/', 'General::index');
		}
	],

	[
		'extension',
		[
			'namespace' => '\BAPI\Controllers\Extension',
			'filter' => 'NknAuth:extension'
		],
		function( $routes )
		{
			$routes->get( '/', 'Crud::index');
			$routes->post( 'crud', 'Crud::create');
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1');
			$routes->delete( 'crud/(:num)', 'Crud::delete/$1');
		}
	]
];

$routes->group( 'backend', $bapiOptions, function( $routes ) use ( $routeData, $bapiPermData )
{

	$routes->get( '/', 'Home::index' );

	# --- Profile ---
	$routes->get( 'profile/(:num)/edit', 'User\Profile::show/$1' );

	$routes->put( 'profile/(:num)', 'User\Profile::update/$1' );

	# --- Nested group ---
	// $c = count( $routeData );
	// for ( $i = 0; $i < $c; $i++ ) {
	// 	$routes->group( $routeData[  $i ][ 0 ], $routeData[  $i ][ 1 ], $routeData[  $i ][ 2 ]  );
	// }

	foreach ( $routeData as $route ) {
		$routes->group( $route[ 0 ], $route[ 1 ], $route[ 2 ]  );
	}
});