<?php

declare( strict_types = 1 );

namespace Config;

use App\Libraries\DesignPattern\Registry;
use App\Libraries\Extension;
use App\Libraries\NknAuth\{
	Config as AuthConfig,
	NknAuthSession as AuthSession
};

use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Session\Session;

class Services extends CoreServices
{

	/**
	 * @return \App\Libraries\NknAuth
	 */
	public static function NknAuth ( AuthConfig $config = null, bool $getShared = true )
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'NknAuth', $config );
		}

		if ( ! is_object( $config ) ) {
			$config = config( AuthConfig::class );
		}

		return new \App\Libraries\NknAuth( $config );
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

		$session = new AuthSession( $driver, $config );
		$session->setLogger( $logger );

		if (session_status() === PHP_SESSION_NONE) { $session->start(); }

		return $session;
	}
}