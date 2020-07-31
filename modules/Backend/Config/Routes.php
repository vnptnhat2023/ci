<?php

namespace BAPI\Config;

# Option array[ controller, placeholder, only, except, websafe ]
# only & except: ['index', 'show', 'create', 'update', 'delete', 'new', 'edit']

# === BAPI ===
$routes->group( 'bapi', [ 'namespace' => '\BAPI\Controllers',
'filter' => 'nknAuth' ], function( $routes )
{
	# ___ Index ___
	$routes->get( '/', 'Home::index' );

	# ___ Profile ___
	$routes->get( 'profile/(:num)/edit', 'User\Profile::show/$1' );
	$routes->put( 'profile/(:num)', 'User\Profile::update/$1' );

	# === User ===
	$routes->group( 'User', [ 'namespace' => '\BAPI\Controllers\User',
	'filter' => 'nknAuth:all' ], function( $routes )
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
		$routes->resource( 'setting', [
			'placeholder' => '(:dashAlpha)',
			'only' => [ 'index', 'create', 'update', 'delete' ]
		] );
	});

	# === POST ===
	$routes->group( 'post', [ 'namespace' => '\BAPI\Controllers\Post',
	'filter' => 'nknAuth:post' ], function( $routes )
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
	});

	# === PAGE ===
	$routes->group( 'page', [ 'namespace' => '\BAPI\Controllers\Page',
	'filter' => 'nknAuth:page' ], function( $routes )
	{
		$routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:num)', 'Crud::update/$1' );
	});

	# === CATEGORY ===
	$routes->group( 'category', [ 'namespace' => 'BAPI\Controllers\Category',
	'filter' => 'nknAuth:category' ], function( $routes )
	{
		$routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
	});

	# === General Group ===
	$routes->group( 'general_group', [ 'namespace' => 'BAPI\Controllers\GeneralGroup',
	'filter' => 'nknAuth:general_group' ], function( $routes )
	{
		// $routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
	});

	# === General Item ===
	$routes->group( 'general_item', [ 'namespace' => 'BAPI\Controllers\GeneralItem',
	'filter' => 'nknAuth:general_item' ], function( $routes )
	{
		// $routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
	});

	# === General Relation ===
	$routes->group( 'general_relation', [ 'namespace' => 'BAPI\Controllers\GeneralRelation',
	'filter' => 'nknAuth:general_relation' ], function( $routes )
	{
		$routes->get( '/', 'GroupItem::index');
	});

	# === General Theme ===
	$routes->group( 'general_theme', [ 'namespace' => 'BAPI\Controllers\Theme',
	'filter' => 'nknAuth:general_theme' ], function( $routes )
	{
		$routes->get( '/', 'General::index');
	});

	# === Extension ===
	$routes->group( 'extension', [ 'namespace' => 'BAPI\Controllers\Extension',
	'filter' => 'nknAuth:extension' ], function( $routes )
	{
		$routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create');
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1');
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1');
	});
});