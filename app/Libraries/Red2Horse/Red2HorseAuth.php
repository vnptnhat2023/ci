<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse;

use App\Libraries\Red2Horse\AuthInterface;
use App\Libraries\Red2Horse\Auth\AuthAdapterInterface;

class Red2HorseAuth implements AuthInterface
{

	protected AuthAdapterInterface $auth;

	public function __construct ( AuthAdapterInterface $auth )
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

	/**
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		return $this->auth->getUserdata( $key );
	}

	public function isLoggedIn ( bool $withCookie = false ) : bool
	{
		return $this->auth->isLoggedIn( $withCookie );
	}

	public function getPasswordHash ( string $pass, int $cost = 12 ) : string
	{
		return $this->auth->getPasswordHash( $pass, $cost );
	}

	public function getMessage ( array $addMore = [] ) : array
	{
		return $this->auth->getMessage( $addMore );
	}

	public function withPermission ( array $data ) : bool
	{
		return $this->auth->withPermission( $data );
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