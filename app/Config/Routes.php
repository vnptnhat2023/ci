<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

$routes->addPlaceholder('dotID', '(\d+\.?)+');#(\d\.?)+\d
$routes->addPlaceholder('dashAlpha', '[a-z_-]+');

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

$routes->cli( 'createdb', 'CLI_CreateDB::index' );
$routes->cli('test', 'Test::index');

$extCachedName = config( '\BAPI\Config\Extension' ) ->getSetting( 'cache' );
if ( $extCached = cache( "{$extCachedName[ 'prefix' ]}{$extCachedName[ 'name' ]}" ) ) {

	foreach ( $extCached[ 'unique_class' ] as $ext ) {
		$extNameSpace = "\\Ext\\{$ext}\\{$ext}";

		if ( method_exists( $extNameSpace, 'getRouter' ) ) {
			$extNameSpace::getRouter( $routes );
		}
	}
}