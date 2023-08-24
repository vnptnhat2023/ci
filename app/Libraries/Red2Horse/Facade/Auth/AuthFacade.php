<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;

/**
 * Not using
 */
class AuthFacade implements AuthFacadeInterface
{

	use TraitSingleton;

	protected AuthFacadeInterface $auth;

	public function __construct ( AuthFacadeInterface $auth )
	{
		$this->auth = $auth;
	}

	public static function eventOn( $eventName, $callback, $priority ) : void
	{
		static::eventOn( $eventName, $callback, $priority );
	}

	public static function eventTrigger( $name, $args )
	{
		return static::eventTrigger( $name, ...$args );
	}

	public function login ( $u = null, $p = null, $r = false, $c = null ) : bool
	{
		return $this->auth->login( $u, $p, $r, $c );
	}

	public function logout () : bool
	{
		return $this->auth->logout();
	}

	public function requestPassword ( $u = null, $e = null, $c = null ) : bool
	{
		return $this->auth->requestPassword( $u, $e, $c );
	}

	public function getUserdata ( $key = null )
	{
		return $this->auth->getUserdata( $key );
	}

	public function isLoggedIn ( $withCookie = false ) : bool
	{
		return $this->auth->isLoggedIn( $withCookie );
	}

	public function getPasswordHash ( $pass ) : string
	{
		return $this->auth->getPasswordHash( $pass );
	}

	public function getMessage ( $addMore = [], $asObject = true )
	{
		return $this->auth->getMessage( $addMore );
	}

	public function withPermission ( $data, $or = true ) : bool
	{
		return $this->auth->withPermission( $data, $or );
	}

	public function withRole ( $role ) : bool
	{
		return $this->auth->withRole( $role );
	}

	public function regenerateCookie () : void
	{
		$this->auth->regenerateCookie();
	}

	public function regenerateSession ( $userData ) : bool
	{
		return $this->auth->regenerateSession( $userData );
	}
}