<?php

declare( strict_types = 1 );
namespace Config;

use CodeIgniter\
{
	Config\Services as CoreServices,
	Session\Session
};

use App\Libraries\{
	DesignPattern\Registry,
	Extension
};

use Red2Horse\{
	R2h,
	Adapter\CodeIgniter\System\Red2HorseSession
};

use function Red2Horse\Mixins\Functions\Config\setConfig;

class Services extends CoreServices
{
	public static function Red2HorseAuth ( bool $getShared = true ) : R2h
	{
		if ( $getShared )
		{
			return static::getSharedInstance( 'Red2HorseAuth' );
		}

		$auth = R2h::getInstance();

		setConfig ( 'BaseConfig', static function( $baseConfig )
		{
			$baseConfig->useRememberMe = true;
			return $baseConfig;
		} );

		setConfig( 'throttle', static function ( $throttle )
		{
			$throttle->useThrottle = false;
			// $throttle->throttle->captchaAttempts = 2;
			// $throttle->useThrottle = true;
			return $throttle;
		} );

		setConfig( 'validation', static function( $valid ) {
			// $valid->user_username = 'qwe_username';
			// $valid->user_password = 'qwe_password';
			return $valid;
		} );

		return $auth;
	}

	public static function Extension ( bool $getShared = true ) : Extension
	{
		if ( $getShared )
		{
			return static::getSharedInstance( 'extension' );
		}

		return new Extension();
	}

	public static function Registry ( $getShared = true ) : Registry
	{
		if ( $getShared )
		{
			return static::getSharedInstance( 'registry' );
		}

		return new Registry();
	}

	public static function session( ?App $config = null, bool $getShared = true ) : Session
	{
		if ( $getShared )
		{
			return static::getSharedInstance( 'session', $config );
		}

		if ( ! is_object( $config ) )
		{
			$config = config( App::class );
		}

		$logger = static::logger();

		$driverName = $config->sessionDriver;
		$driver = new $driverName( $config, static::request() ->getIPAddress() );
		$driver->setLogger( $logger );

		$session = new Red2HorseSession( $driver, $config );
		$session->setLogger( $logger );

		if ( session_status() === PHP_SESSION_NONE )
		{
			$session->start();
		}

		return $session;
	}
}