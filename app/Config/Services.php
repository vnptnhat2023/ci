<?php

declare( strict_types = 1 );

namespace Config;

use App\Libraries\Red2Horse\Facade\Auth\AuthAdapter;
use App\Libraries\Red2Horse\Adapter\Red2Horse\Adapter as R2hAdapter;
use App\Libraries\Red2Horse;
use App\Libraries\Red2Horse\Sys\Red2HorseSession;

use App\Libraries\DesignPattern\Registry;
use App\Libraries\Extension;

// use App\Libraries\NknAuth\{
// 	Config as AuthConfig,
// 	NknAuthSession as AuthSession
// };

use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Session\Session;

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
	 * @return \App\Libraries\Red2Horse\Red2HorseAuth
	 */
	public static function Red2HorseAuth ( Red2Horse\Config $config = null, bool $getShared = true )
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'Red2HorseAuth', $config );
		}

		if ( ! is_object( $config ) ) {
			$config = config( Red2Horse\Config::class );
		}

		$auth = new AuthAdapter ( new Red2Horse\Red2Horse( $config ) );
		return new R2hAdapter( $auth );
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