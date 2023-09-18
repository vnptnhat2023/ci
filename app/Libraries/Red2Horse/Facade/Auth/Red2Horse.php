<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\
{
	TraitSingleton
};

use function Red2Horse\Mixins\Functions\
{
    getInstance
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Red2Horse
{
	use TraitSingleton;

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool
	{
		return getInstance( Authentication::class )->login( $u, $p, $r, $c );
	}

	public function logout () : bool
	{
		return getInstance( Authentication::class )->logout();
	}

	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool
	{
		return getInstance( ResetPassword::class )->requestPassword( $u, $e, $c );
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		return getInstance( Authentication::class )->getUserdata( $key );
	}

	public function isLogged ( bool $withCookie = false ) : bool
	{
		return getInstance( Authentication::class )->isLogged( $withCookie );
	}

	public function getHashPass ( string $password ) : string
	{
		return getInstance( Password::class )->getHashPass( $password );
	}

	public function getVerifyPass ( string $p, string $hashed ) : bool
	{
		return getInstance( Password::class )->getVerifyPass( $p, $hashed );
	}

	/** @return object|array */
	public function getResult ()
	{
		return getInstance( Message::class )->getResult();
	}

	/** @return mixed */
	public function getMessage ( array $add = [], bool $asObject = true )
	{
		return getInstance( Message::class )->getMessage( $add, $asObject );
	}

	public function withPermission ( array $data, bool $or = true ) : bool
	{
		return getInstance( Authorization::class )->run( $data );
	}

	# @Todo: late
	public function withGroup( array $data ) : bool
	{
		return getInstance( Authorization::class )->run( $data );
	}

	public function withRole ( array $role, bool $or = true ) : bool
	{
		return getInstance( Authorization::class )->run( $role );
	}

	public function regenerateSession ( array $userData ) : bool
	{
		return getInstance( SessionHandle::class )->regenerateSession( $userData );
	}

	public function regenerateCookie () : void
	{
		getInstance( CookieHandle::class )->regenerateCookie();
	}
}