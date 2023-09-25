<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getField,
    getInstance
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SessionHandle
{
	use TraitSingleton;

	private function __construct () {}

	public function regenerateSession ( array $userData ) : bool
	{
		if ( ! getInstance( Authentication::class )->isLogged() )
		{
			return false;
		}

		$userID = getField( 'id', 'user' );
		$isUpdated = getComponents( 'user' ) ->updateUser(
			$userID,
			[ getField( 'session_id', 'user' ) => session_id() ]
		);

		if ( ! $isUpdated )
		{
			$errStr = "The session_id: {$userID} update failed";
			getComponents( 'common' ) ->log_message( 'error', $errStr );

			return false;
		}

		getInstance( CookieHandle::class )->regenerateCookie();

		return true;
	}
}