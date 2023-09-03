<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;

use function Red2Horse\Mixins\Functions\{
	getComponents,
	getInstance
};

class Utility
{
	use TraitSingleton;

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

		if ( $authentication->isLogged( true ) )
		{
			$type === 'forget'
				? $message::$incorrectResetPassword = true
				: $authentication->setLoggedInSuccess( $authentication->getUserdata() );

			return true;
		}

		if ( getComponents( 'throttle' )->limited() )
		{
			$errArg = [
				'num' => gmdate( 'i', getInstance( Config::class )->throttle->timeoutAttempts ),
				'type' => 'minutes'
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

	public function trigger ( \Closure $closure, string $event, array $eventData )
	{
		if ( ! isset( $closure->{$event} ) || empty( $closure->{$event} ) )
		{
			return $eventData;
		}

		foreach ( $closure->{$event} as $callback )
		{
			if ( ! method_exists( $closure, $callback ) )
			{
				throw new \Exception( 'Invalid Method Triggered', 403 );
			}

			$eventData = $closure->{ $callback }( $eventData );
		}

		return $eventData;
	}
}