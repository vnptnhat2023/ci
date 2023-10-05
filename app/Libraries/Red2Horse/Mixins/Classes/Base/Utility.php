<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    baseInstance
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Utility
{
	use TraitSingleton;

	protected array $type = [ 'login', 'forgot', 'forget' ];

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
		if ( ! in_array( $type, $this->type ) )
		{
			throw new \Error( 'Invalid type checker."', 403 );
		}

		$requestType = ( $type == 'login' ) ? 'password' : 'email';

		$isNullUsername = null === $u;
		$isNullType = null === $requestType;
		$hasRequest = ! $isNullUsername && ! $isNullType;

		$authentication = baseInstance( Authentication::class );
		$message = baseInstance( Message::class );

		if ( $authentication->isLogged() || $authentication->isLogged( true ) )
		{
			$type == 'forget'
				? baseInstance( ResetPassword::class )
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
			: baseInstance( ResetPassword::class )->forgetHandler( $u, $e, $c );
	}
}