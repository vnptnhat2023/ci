<?php namespace BAPI\Config;

use Config\Services;
use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();
# Option array[ controller, placeholder, only, except, websafe ]

$bapiPermData = [
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
	'filter' => "R2hAuth:{$bapiPermStr}"
];

$routes->group( 'backend', $bapiOptions, function ( RouteCollection $routes )
{
	$groupOptions = fn ( string $lastSegment, string $permission ) => [
		'namespace' => "\\BAPI\\Controllers\\{$lastSegment}",
		'filter' => "R2hAuth:{$permission}"
	];

	$routes->get( '/', 'Home::index' );

	# ___ Profile ___
	$routes->get( 'profile/(:num)/edit', 'Profile::show/$1' );
	$routes->put( 'profile/(:num)', 'Profile::update/$1' );

	$routes->group( 'user', $groupOptions( 'User', 'all' ), function ( RouteCollection $routes )
	{
		$crudOption = [ 'except' => 'update,delete', 'placeholder' => '(:num)' ];
		$routes->resource( 'crud', $crudOption );
		$routes->delete( 'crud/(:dotID)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );

		$routes->resource( 'group', [
			'placeholder' => '(:num)',
			'only' => [ 'index', 'create', 'delete', 'update' ]
		] );

		$routes->resource( 'setting', [
			'placeholder' => '(:dashAlpha)',
			'only' => [ 'index', 'create', 'update', 'delete' ]
		] );
	});

	$routes->group( 'post', $groupOptions( 'Post', 'post' ), function ( RouteCollection $routes )
	{
		$crudOption = [ 'except' => 'new,update,delete', 'placeholder' => '(:num)' ];

		$routes->resource( 'crud', $crudOption );
		$routes->delete( 'crud/(:dotID)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );

		$routes->resource( 'setting', [
			'placeholder' => '(:dashAlpha)',
			'only' => [ 'index', 'create', 'update', 'delete' ]
		] );
	});

	$routes->group( 'page', $groupOptions( 'Page', 'page' ), fn ( RouteCollection $routes ) =>
		$routes->resource( 'crud', [ 'placeholder' => '(:num)', 'except' => 'edit,new,show' ] )
	);

	$routes->group( 'category',
		$groupOptions( 'Category', 'category' ),
		function ( RouteCollection $routes ) {
			$option = [ 'only' => 'index,create,delete', 'placeholder' => '(:num)' ];
			$routes->resource( 'crud', $option );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	);

	$routes->group( 'general_group',
		$groupOptions ( 'GeneralGroup', 'general_group' ),
		function ( RouteCollection $routes ) {
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	);

	$routes->group( 'general_item',
		$groupOptions ( 'GeneralItem', 'general_item' ),
		function ( RouteCollection $routes ) {
			$routes->post( 'crud', 'Crud::create' );
			$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$routes->put( 'crud/(:num)', 'Crud::update/$1' );
			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	);

	$routes->group( 'general_relation',
		$groupOptions ( 'GeneralRelation', 'general_relation' ),
		fn ( RouteCollection $routes ) => $routes->get( '/', 'GroupItem::index')
	);

	$routes->group( 'general_theme',
		$groupOptions ( 'Theme', 'general_theme' ),
		fn ( RouteCollection $routes ) => $routes->get( '/', 'General::index')
	);

	$routes->group( 'extension',
		$groupOptions ( 'Extension', 'extension.r, extension.c, extension.u' ),
		// $groupOptions ( 'Extension', 'extension' ),
		function ( RouteCollection $routes ) {
			$option = [ 'only' => 'index,create,delete', 'placeholder' => '(:num)' ];
			$routes->resource( 'crud', $option );

			$routes->patch( 'crud/(:dotID)', 'Crud::update/$1');
		}
	);

});