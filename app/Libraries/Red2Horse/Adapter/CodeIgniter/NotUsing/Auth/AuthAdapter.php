<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\CodeIgniter\Auth;

use Red2Horse\Facade\Auth\Red2Horse;
use CodeIgniter\Events\Events;

class AuthAdapter implements AuthAdapterInterface
{
	protected Red2Horse $auth;

	public function __construct ( Red2Horse $auth )
	{
		$this->auth = $auth;
	}

	public static function eventOn ( $name, $callback, $priority = EVENT_PRIORITY_NORMAL ) : void
	{
		Events::on( $name, $callback, $priority );
	}

	public static function eventTrigger ( $name, $args )
	{
		return Events::trigger( $name, ...$args );
	}

	public function login ( $u = null, $p = null, $r = false, $c = null ) : bool
	{
		return $this->auth->login( $u, $p, $r, $c );
	}

	public function requestPassword ( $u = null, $e = null, $c = null ) : bool
	{
		return $this->auth->requestPassword( $u, $e, $c );
	}

	public function logout () : bool
	{
		return $this->auth->logout();
	}

	public function getUserdata ( $key = null )
	{
		return $this->auth->getUserdata( $key );
	}

	public function isLoggedIn ( $withCookie = false ) : bool
	{
		return $this->auth->isLogged( $withCookie );
	}

	public function getPasswordHash ( $pass ) : string
	{
		return $this->auth->getHashPass( $pass );
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