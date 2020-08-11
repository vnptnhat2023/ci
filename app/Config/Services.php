<?php namespace Config;

use App\Libraries\NknAuth\NknAuthSession;
use App\Libraries\NknAuth\NknFileHandler;
use CodeIgniter\Config\Services as CoreServices;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
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

		# Make a ci config passing down to construct
		# Auth config check type hint ci config
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