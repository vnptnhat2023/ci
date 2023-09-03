<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;

use function Red2Horse\Mixins\Functions\{
	getComponents,
	getInstance
};

class SessionHandle
{
	use TraitSingleton;

	public function regenerateSession ( array $userData ) : bool
	{
		if ( ! getInstance( Authentication::class )->isLogged() )
		{
			return false;
		}

		$isUpdated = getComponents( 'user' ) ->updateUser(
			$userData[ 'id' ],
			[ 'session_id' => session_id() ]
		);

		if ( ! $isUpdated ) {
			$errStr = "The session_id: {$userData[ 'id' ]} update failed";
			getComponents( 'common' ) ->log_message( 'error', $errStr );

			return false;
		}

		getInstance( CookieHandle::class )->regenerateCookie();

		return true;
	}
}