<?php namespace Config;

use App\Libraries\NknAuth\{ Config as AuthConfig, NknAuthSession as AuthSession };
use CodeIgniter\Config\Services as CoreServices;

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

	/**
	 * @return \App\Libraries\Extension
	 */
	public static function Extension ( bool $getShared = true )
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'extension' );
		}

		return new \App\Libraries\Extension();
	}

	/**
	 * @return \CodeIgniter\Session\Session
	 */
	public static function session( App $config = null, bool $getShared = true )
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