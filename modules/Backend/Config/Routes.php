<?php

namespace BAPI\Config;

/**
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

# Option array[ controller, placeholder, only, except, websafe ]
# only & except: ['index', 'show', 'create', 'update', 'delete', 'new', 'edit']

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
	'filter' => "NknAuth:{$bapiPermStr}"
];

# === BAPI ===
$routes->group( 'bapi', $bapiOptions, function( $routes )
{

	$routes->get( '/', 'Home::index' );

	# ___ Profile ___
	$routes->get( 'profile/(:num)/edit', 'User\Profile::show/$1' );
	$routes->put( 'profile/(:num)', 'User\Profile::update/$1' );

	# === User ===
	$user = [
		'namespace' => '\BAPI\Controllers\User',
		'filter' => 'NknAuth:all'
	];

	$routes->group( 'user', $user, function( $routes )
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
	});

	# === POST ===
	$post = [
		'namespace' => '\BAPI\Controllers\Post',
		'filter' => 'NknAuth:post'
	];

	$routes->group( 'post', $post, function( $routes )
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
	$page = [
		'namespace' => '\BAPI\Controllers\Page',
		'filter' => 'NknAuth:page'
	];

	$routes->group( 'page', $page, function( $routes )
	{
		$routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:num)', 'Crud::update/$1' );
	});

	# === CATEGORY ===
	$category = [
		'namespace' => '\BAPI\Controllers\Category',
		'filter' => 'NknAuth:category'
	];

	$routes->group( 'category', $category, function( $routes )
	{
		$routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
	});

	# === General Group ===
	$generalGroup = [
		'namespace' => '\BAPI\Controllers\GeneralGroup',
		'filter' => 'NknAuth:general_group'
	];

	$routes->group( 'general_group', $generalGroup, function( $routes )
	{
		// $routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
	});

	# === General Item ===
	$generalItem = [
		'namespace' => '\BAPI\Controllers\GeneralItem',
		'filter' => 'NknAuth:general_item'
	];

	$routes->group( 'general_item', $generalItem, function( $routes )
	{
		// $routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create' );
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1' );
		$routes->put( 'crud/(:num)', 'Crud::update/$1' );
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1' );
	});

	# === General Relation ===
	$generalRelation = [
		'namespace' => '\BAPI\Controllers\GeneralRelation',
		'filter' => 'NknAuth:general_relation'
	];

	$routes->group( 'general_relation', $generalRelation, function( $routes )
	{
		$routes->get( '/', 'GroupItem::index');
	});

	# === General Theme ===
	$generalTheme = [
		'namespace' => '\BAPI\Controllers\Theme',
		'filter' => 'NknAuth:general_theme'
	];

	$routes->group( 'general_theme', $generalTheme, function( $routes )
	{
		$routes->get( '/', 'General::index');
	});

	# === Extension ===
	$extension = [
		'namespace' => '\BAPI\Controllers\Extension',
		'filter' => 'NknAuth:extension'
	];

	$routes->group( 'extension', $extension, function( $routes )
	{
		$routes->get( '/', 'Crud::index');
		$routes->post( 'crud', 'Crud::create');
		$routes->patch( 'crud/(:dotID)', 'Crud::update/$1');
		$routes->delete( 'crud/(:num)', 'Crud::delete/$1');
	});
});