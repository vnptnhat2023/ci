<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Auth;

use App\Libraries\Red2Horse\Red2Horse;

class Adapter implements AdapterInterface
{
	protected Red2Horse $CiAuth;

	public function __construct ( Red2Horse $CiAuth )
	{
		$this->CiAuth = $CiAuth;
	}

	public function login (
		string $username = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	): bool
	{
		return $this->CiAuth->login( $username, $password, $rememberMe, $captcha );
	}

	public function logout () : bool
	{
		return $this->CiAuth->logout();
	}

	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool
	{
		return $this->CiAuth->requestPassword( $username, $email );
	}

	public function getUserdata ( string $key = null )
	{
		return $this->CiAuth->getUserdata( $key );
	}

	public function isLoggedIn ( bool $withCookie = false ) : bool
	{
		return $this->CiAuth->isLogged( $withCookie );
	}

	public function getPasswordHash ( string $pass, int $cost = 12 ) : string
	{
		return $this->CiAuth->getHashPass( $pass, $cost );
	}

	public function getMessage ( array $addMore = [] ) : array
	{
		return $this->CiAuth->getMessage( $addMore );
	}

	public function withPermission ( array $data ) : bool
	{
		return $this->CiAuth->hasPermission( $data );
	}

	public function regenerateCookie () : void
	{
		$this->CiAuth->regenerateCookie();
	}

	public function regenerateSession ( array $userData ) : bool
	{
		return $this->CiAuth->regenerateSession( $userData );
	}

}