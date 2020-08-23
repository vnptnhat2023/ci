<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Auth;

use App\Libraries\Red2Horse\Red2Horse;

class CiAuthAdapter implements AuthInterface
{
	protected Red2Horse $auth;

	public function __construct ( Red2Horse $auth )
	{
		$this->auth = $auth;
	}

	public function login (
		string $username = null,
		string $password,
		bool $rememberMe = false
	): bool
	{
		return $this->auth->login( $username, $password, $rememberMe );
	}

	public function logout () : array
	{
		return $this->auth->logout();
	}

	public function requestPassword (
		string $username,
		string $email,
		bool $returnType = true
	) : bool
	{
		return $this->auth->requestPassword( $username, $email );
	}

	public function getUserdata ( string $key = null )
	{
		return $this->auth->getUserdata( $key );
	}

	public function isLoggedIn ( bool $withCookie = false ) : bool
	{
		return $this->auth->isLogged( $withCookie );
	}

	public function getPasswordHash ( string $pass, int $cost = 12 ) : string
	{
		return $this->auth->getHashPass( $pass, $cost );
	}

	public function getMessage ( array $addMore = [] ) : array
	{
		return $this->auth->getMessage( $addMore );
	}

	public function withPermission ( array $data ) : bool
	{
		return $this->auth->hasPermission( $data );
	}

	public function regenerateCookie () : void
	{
		$this->auth->setTestCookie();
	}

}