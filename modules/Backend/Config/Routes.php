<?php

namespace BAPI\Config;

use Config\Services;
use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();
$bapiOptions = [
	'namespace' => '\BAPI\Controllers',
	'filter' => 'r2h_role: administrator'
];

$routes->group( 'backend', $bapiOptions, function ( RouteCollection $r )
{
	$options = function ( string $segment, array $keys, $filter = 'role' )
	{
		$filter = ( $filter == 'role' ) ? 'r2h_role' : 'r2h_permission';
		$key = strtolower( implode( ',', $keys ) );

		return [
			'namespace' => "\\BAPI\\Controllers\\{$segment}",
			'filter' => "{$filter}: {$key}"
		];
	};

	$r->get( '/', 'Home::index' );

	# ___ Profile ___
	$r->get( 'profile/(:num)/edit', 'Profile::show/$1' );
	$r->put( 'profile/(:num)', 'Profile::update/$1' );

	$r->group( 'user', $options( 'User', [ 'administrator' ] ), function ( RouteCollection $r )
	{
		$crudOption = [ 'except' => 'update,delete', 'placeholder' => '(:num)' ];
		$r->resource( 'crud', $crudOption );
		$r->delete( 'crud/(:dotID)', 'Crud::delete/$1' );
		$r->put( 'crud/(:num)', 'Crud::update/$1' );
		$r->patch( 'crud/(:dotID)', 'Crud::update/$1' );

		$r->resource( 'group', [
			'placeholder' => '(:num)',
			'only' => [ 'index', 'create', 'delete', 'update' ]
		] );

		$r->resource( 'setting', [
			'placeholder' => '(:dashAlpha)',
			'only' => [ 'index', 'create', 'update', 'delete' ]
		] );
	});

	$r->group( 'post', $options( 'Post', [ 'administrator', 'post' ] ), function ( RouteCollection $r )
	{
		$crudOption = [ 'except' => 'new,update,delete', 'placeholder' => '(:num)' ];

		$r->resource( 'crud', $crudOption );
		$r->delete( 'crud/(:dotID)', 'Crud::delete/$1' );
		$r->put( 'crud/(:num)', 'Crud::update/$1' );
		$r->patch( 'crud/(:dotID)', 'Crud::update/$1' );

		$r->resource( 'setting', [
			'placeholder' => '(:dashAlpha)',
			'only' => [ 'index', 'create', 'update', 'delete' ]
		] );
	});

	$r->group( 'page', $options( 'Page', [ 'page' ], 'permission' ), fn ( RouteCollection $r ) =>
		$r->resource( 'crud', [ 'placeholder' => '(:num)', 'except' => 'edit,new,show' ] )
	);

	$r->group( 'category',
		$options( 'Category', [ 'category' ], 'permission' ),
		function ( RouteCollection $r ) {
			$option = [ 'only' => 'index,create,delete', 'placeholder' => '(:num)' ];
			$r->resource( 'crud', $option );
			$r->put( 'crud/(:num)', 'Crud::update/$1' );
			$r->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	);

	$r->group( 'general_group',
		$options ( 'GeneralGroup', [ 'general_group' ], 'permission' ),
		function ( RouteCollection $r ) {
			$r->post( 'crud', 'Crud::create' );
			$r->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$r->put( 'crud/(:num)', 'Crud::update/$1' );
			$r->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	);

	$r->group( 'general_item',
		$options ( 'GeneralItem', [ 'general_item' ], 'permission' ),
		function ( RouteCollection $r ) {
			$r->post( 'crud', 'Crud::create' );
			$r->delete( 'crud/(:num)', 'Crud::delete/$1' );
			$r->put( 'crud/(:num)', 'Crud::update/$1' );
			$r->patch( 'crud/(:dotID)', 'Crud::update/$1' );
		}
	);

	$r->group( 'general_relation',
		$options ( 'GeneralRelation', [ 'general_relation' ], 'permission' ),
		fn ( RouteCollection $r ) => $r->get( '/', 'GroupItem::index')
	);

	$r->group( 'general_theme',
		$options ( 'Theme', [ 'general_theme' ], 'permission' ),
		fn ( RouteCollection $r ) => $r->get( '/', 'General::index')
	);

	// $options ( 'Extension', 'extension.r, extension.d, extension.u' ),
	$r->group( 'extension',
		$options ( 'Extension', [ 'extension' ], 'permission' ),
		function ( RouteCollection $r ) {
			$option = [ 'only' => 'index,create,delete', 'placeholder' => '(:num)' ];
			$r->resource( 'crud', $option );

			$r->patch( 'crud/(:dotID)', 'Crud::update/$1');
		}
	);

});