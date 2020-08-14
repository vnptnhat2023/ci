<?php namespace Config;

use App\Libraries\NknAuth\NknAuthSession;
use CodeIgniter\Config\Services as CoreServices;

class Services extends CoreServices
{

	/**
	 * @return \App\Libraries\NknAuth
	 */
	public static function NknAuth ( bool $getShared = true )
	{
		if ( $getShared === true ) {
			return static::getSharedInstance( 'NknAuth' );
		}

		return new \App\Libraries\NknAuth();
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

		$session = new NknAuthSession( $driver, $config );
		$session->setLogger( $logger );

		if (session_status() === PHP_SESSION_NONE) { $session->start(); }

		return $session;
	}
}