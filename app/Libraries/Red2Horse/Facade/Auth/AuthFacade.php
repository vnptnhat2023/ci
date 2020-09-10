<?php

declare( strict_types = 1 );
namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

# --------------------------------------------------------------------------

class AuthFacade implements AuthFacadeInterface
{

	use TraitSingleton;

	protected AuthFacadeInterface $auth;

	# ------------------------------------------------------------------------

	public function __construct ( AuthFacadeInterface $auth )
	{
		$this->auth = $auth;
	}

	# ------------------------------------------------------------------------

	public function login (
		string $username = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	): bool
	{
		return $this->auth->login( $username, $password, $rememberMe, $captcha );
	}

	public function logout () : bool
	{
		return $this->auth->logout();
	}

	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
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
		return $this->auth->isLoggedIn( $withCookie );
	}

	public function getPasswordHash ( string $pass ) : string
	{
		return $this->auth->getPasswordHash( $pass );
	}

	public function getMessage ( array $addMore = [], bool $asObject = true )
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