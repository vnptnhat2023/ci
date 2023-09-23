<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    getInstance
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Utility
{
	use TraitSingleton;

	private function __construct () {}

	public function typeChecker
	(
		$type = 'login',
		string $u = null,
		string $password = null,
		string $e = null,
		string $c = null
	) : bool
	{
		if ( ! in_array( $type, [ 'login', 'forget' ] ) )
		{
			throw new \Exception( 'Invalid type checker."', 403 );
		}

		$requestType = ( $type === 'login' ) ? 'password' : 'email';

		$isNullUsername = null === $u;
		$isNullType = null === $requestType;
		$hasRequest = ! $isNullUsername && ! $isNullType;

		$authentication = getInstance( Authentication::class );
		$message = getInstance( Message::class );

		if ( $authentication->isLogged() || $authentication->isLogged( true ) )
		{
			$type === 'forget'
				? getInstance( ResetPassword::class )
					->alreadyLoggedIn( $authentication->getUserdata() )
				: $authentication
					->setLoggedInSuccess( $authentication->getUserdata() );

			return true;
		}

		if ( getComponents( 'throttle' )->limited() )
		{
			$errArg = [
				'number' => gmdate( 'i', getConfig( 'throttle' )->throttle->timeoutAttempts ),
				'minutes' => 'minutes'
			];
			$errStr = getComponents( 'common' )->lang( 'Red2Horse.errorThrottleLimitedTime', $errArg );
			$message::$errors[] = $errStr;

			return false;
		}

		if ( ! $hasRequest )
		{
			return false;
		}

		return ( $type === 'login' )
			? $authentication->loginHandler()
			: getInstance( ResetPassword::class )->forgetHandler( $u, $e, $c );
	}

	// public function trigger ( \Closure $closure, string $event, array $eventData )
	// {
	// 	if ( ! isset( $closure->{$event} ) || empty( $closure->{$event} ) )
	// 	{
	// 		return $eventData;
	// 	}

	// 	foreach ( $closure->{$event} as $callback )
	// 	{
	// 		if ( ! method_exists( $closure, $callback ) )
	// 		{
	// 			throw new \Exception( 'Invalid Method Triggered', 403 );
	// 		}

	// 		$eventData = $closure->{ $callback }( $eventData );
	// 	}

	// 	return $eventData;
	// }
}