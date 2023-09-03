<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\
{
	TraitSingleton,
	TraitCall
};

use function Red2Horse\Mixins\Functions\
{
	callClass
};

class Red2Horse
{
	use TraitSingleton, TraitCall;

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool
	{
		return callClass( Authentication::class, false )->login( $u, $p, $r, $c );
	}

	public function logout () : bool
	{
		return callClass( Authentication::class, false )->logout();
	}

	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool
	{
		return callClass( ResetPassword::class, false )->requestPassword( $u, $e, $c );
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		return callClass( Authentication::class, false )->getUserdata( $key );
	}

	public function getHashPass ( string $password ) : string
	{
		return callClass( Password::class, false )->getHashPass( $password );
	}

	public function getVerifyPass ( string $p, string $hashed ) : bool
	{
		return callClass( Password::class, false )->getVerifyPass( $p, $hashed );
	}

	/** @return object|array */
	public function getResult ()
	{
		return callClass( Message::class, false )->getResult();
	}

	public function getMessage ( array $add = [], bool $asObject = true )
	{
		return callClass( Message::class, false )->getMessage( $add, $asObject );
	}

	public function withPermission ( array $data, bool $or = true ) : bool
	{
		return callClass( Authorization::class, false )->run( $data );
	}

	# @Todo: late
	public function withGroup( array $data ) : bool
	{
		return callClass( Authorization::class, false )->run( $data );
	}

	public function withRole ( array $role, bool $or = true ) : bool
	{
		return callClass( Authorization::class, false )->run( $role );
	}

	public function isLogged ( bool $withCookie = false ) : bool
	{
		return callClass( Authentication::class, false )->isLogged( $withCookie );
	}

	public function regenerateSession ( array $userData ) : bool
	{
		return callClass( SessionHandle::class, false )->regenerateSession( $userData );
	}

	public function regenerateCookie () : void
	{
		callClass( CookieHandle::class, false )->regenerateCookie();
	}
}