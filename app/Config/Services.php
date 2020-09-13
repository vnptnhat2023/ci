<?php

declare( strict_types = 1 );

namespace Config;

use App\Libraries\Red2Horse\Facade\Auth\AuthFacade;
use App\Libraries\Red2Horse\Facade\Auth\Config as r2hConfig;
use App\Libraries\Red2Horse\Sys\Red2HorseSession;
use App\Libraries\Red2Horse\Facade\Auth\Red2HorseFacade;

use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Session\Session;

use App\Libraries\DesignPattern\Registry;
use App\Libraries\Extension;

class Services extends CoreServices
{
	/**
	 * @return AuthFacade
	 */
	public static function Red2HorseAuth ( r2hConfig $config = null, bool $getShared = true )
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'Red2HorseAuth', $config );
		}

		if ( ! is_object( $config ) ) {
			$config = r2hConfig::getInstance();
		}

		$authAdapter = $config->adapter();

		return AuthFacade::getInstance(
			new $authAdapter( Red2HorseFacade::getInstance( $config ) )
		);
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