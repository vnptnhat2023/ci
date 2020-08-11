<?php

namespace App\Libraries\NknAuth;

use CodeIgniter\Config\Services as CoreServices;
use Config\App;

class Services extends CoreServices
{
	// public static function n1ciSession ( App $config = null, bool $getShared = true )
	// {
	// 	if ( $getShared )
	// 	{
	// 		return static::getSharedInstance( 'n1ci_session', $config );
	// 	}

	// 	if ( ! is_object( $config ) )
	// 	{
	// 		$config = config( App::class );
	// 	}

	// 	$logger = static::logger();

	// 	$driverName = $config->sessionDriver;
	// 	$driver = new $driverName( $config, static::request() ->getIPAddress() );
	// 	$driver->setLogger( $logger );

	// 	$session = new \App\Libraries\NknAuth\n1ciSession( $driver, $config );
	// 	$session->setLogger( $logger );

	// 	if ( session_status() === PHP_SESSION_NONE )
	// 	{
	// 		$session->start();
	// 	}

	// 	return $session;
	// }
}