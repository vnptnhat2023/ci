<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Auth;

use App\Libraries\Red2Horse\Facade\Auth\Red2Horse;

class AuthAdapter implements AuthAdapterInterface
{
	protected Red2Horse $auth;

	public function __construct ( Red2Horse $auth )
	{
		$this->auth = $auth;
	}

	public function login (
		string $username = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	) : bool
	{
		return $this->auth->login( $username, $password, $rememberMe, $captcha );
	}

	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool
	{
		return $this->auth->requestPassword( $username, $email, $captcha );
	}

	public function logout () : bool
	{
		return $this->auth->logout();
	}

	public function getUserdata ( string $key = null )
	{
		return $this->auth->getUserdata( $key );
	}

	public function isLoggedIn ( bool $withCookie = false ) : bool
	{
		return $this->auth->isLogged( $withCookie );
	}

	public function getPasswordHash ( string $pass ) : string
	{
		return $this->auth->getHashPass( $pass );
	}

	public function getMessage ( array $addMore = [], bool $asObject = true )
	{
		return $this->auth->getMessage( $addMore );
	}

	public function withPermission ( array $data ) : bool
	{
		return $this->auth->withPermission( $data );
	}

	public function withRole ( array $role ) : bool
	{
		return $this->auth->withRole( $role );
	}

	public function regenerateCookie () : void
	{
		$this->auth->regenerateCookie();
	}

	public function regenerateSession ( array $userData ) : bool
	{
		return $this->auth->regenerateSession( $userData );
	}
}