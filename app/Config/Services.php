<?php

declare( strict_types = 1 );

namespace Config;

use App\Libraries\Red2Horse\Facade\Auth\AuthFacade;
// use App\Libraries\Red2Horse\Config as r2hConfig;
use App\Libraries\Red2Horse\Facade\Auth\Config as r2hConfig;
use App\Libraries\Red2Horse\Sys\Red2HorseSession;
// use App\Libraries\Red2Horse\Red2Horse;
use App\Libraries\Red2Horse\Facade\Auth\Red2HorseFacade;

// use App\Libraries\NknAuth\{
// 	Config as AuthConfig,
// 	NknAuthSession as AuthSession
// };

use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Session\Session;

use App\Libraries\DesignPattern\Registry;
use App\Libraries\Extension;

class Services extends CoreServices
{

	/*public static function NknAuth ( AuthConfig $config = null, bool $getShared = true )
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'NknAuth', $config );
		}

		if ( ! is_object( $config ) ) {
			$config = config( AuthConfig::class );
		}

		return new \App\Libraries\NknAuth( $config );
	}*/

	/**
	 * @return AuthFacade
	 */
	public static function Red2HorseAuth ( r2hConfig $config = null, bool $getShared = true )
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'Red2HorseAuth', $config );
		}

		if ( ! is_object( $config ) ) {
			$config = new r2hConfig;
		}

		$adapterName = $config->adapter();
		// die(var_dump( new $adapterName( new Red2HorseFacade( $config ) ) ));
		return new AuthFacade(
			new $adapterName(
				new Red2HorseFacade( $config )
			)
		);

		// $adapter = new $config->authAdapter ( new Red2Horse( $adapter ) );
		// return new AuthFacade( $adapter );
	}

	public static function Extension ( bool $getShared = true ) : Extension
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'extension' );
		}

		return new \App\Libraries\Extension();
	}

	public static function Registry ( $getShared = true ) : Registry
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'registry' );
		}

		return new \App\Libraries\DesignPattern\Registry();
	}

	public static function session( App $config = null, bool $getShared = true ) : Session
	{
		if ( $getShared ) return static::getSharedInstance( 'session', $config );

		if ( ! is_object( $config ) ) { $config = config( App::class ); }

		$logger = static::logger();

		$driverName = $config->sessionDriver;
		$driver = new $driverName( $config, static::request() ->getIPAddress() );
		$driver->setLogger($logger);

		$session = new Red2HorseSession( $driver, $config );
		$session->setLogger( $logger );

		if (session_status() === PHP_SESSION_NONE) { $session->start(); }

		return $session;
	}
}