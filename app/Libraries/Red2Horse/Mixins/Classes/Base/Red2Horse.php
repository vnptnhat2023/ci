<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Auth\withSession;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Red2Horse
{
	use TraitSingleton;

	private function __construct () {}

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool
	{
		return getBaseInstance( Authentication::class )->login( $u, $p, $r, $c );
	}

	public function logout () : bool
	{
		return getBaseInstance( Authentication::class )->logout();
	}

	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool
	{
		return getBaseInstance( ResetPassword::class )->requestPassword( $u, $e, $c );
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		return getBaseInstance( Authentication::class )->getUserdata( $key );
	}

	public function isLogged ( bool $withCookie = false ) : bool
	{
		return getBaseInstance( Authentication::class )->isLogged( $withCookie );
	}

	public function getHashPass ( string $password ) : string
	{
		return getBaseInstance( Password::class )->getHashPass( $password );
	}

	public function getVerifyPass ( string $p, string $hashed ) : bool
	{
		return getBaseInstance( Password::class )->getVerifyPass( $p, $hashed );
	}

	/** @return object|array */
	public function getResult ()
	{
		return getBaseInstance( Message::class )->getResult();
	}

	/** @return mixed */
	public function getMessage ( array $add = [], bool $asObject = true )
	{
		return getBaseInstance( Message::class )->getMessage( $add, $asObject );
	}

	public function withPermission ( array $data, string $condition = 'or' ) : bool
	{
		return withSession( 'permission', $data, $condition );
	}

	public function withRole ( array $data, string $condition = 'or' ) : bool
	{
		return withSession( 'role', $data, $condition );
	}

	public function regenerateSession ( array $userData ) : bool
	{
		return getBaseInstance( SessionHandle::class )->regenerateSession( $userData );
	}

	public function regenerateCookie () : void
	{
		getBaseInstance( CookieHandle::class )->regenerateCookie();
	}
}