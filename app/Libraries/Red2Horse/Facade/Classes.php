<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade;

use App\Libraries\Red2Horse\Facade\AuthInterface;
use App\Libraries\Red2Horse\Auth\AuthInterface as AuthAdapterInterface;
use App\Libraries\Red2Horse\Validation\ValidationInterface;

/**
 * Validation adapter class
 *
 * ---
 *
 * ```
 * new CodeigniterValidation( config( 'validation' ), config( 'request' ) )
 * ```
 */
class Validate implements ValidateInterface
{

	protected ValidationInterface $validationAdapter;

	public function __construct ( ValidationInterface $validate )
	{
		$this->validationAdapter = $validate;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate->isValid( $data, $rules );
	}

	public function getErrors ( string $field = null ) : array
	{
		return $this->validate->getErrors( $field );
	}
}


class Auth implements AuthInterface
{

	protected AuthAdapterInterface $auth;

	public function __construct ( AuthAdapterInterface $auth )
	{
		$this->auth = $auth;
	}

	public function login (
		string $username = null,
		string $password,
		bool $rememberMe = false
	) : bool
	{
		return $this->auth->login( $username, $password, $rememberMe );
	}

	public function requestPassword (
		string $username,
		string $email,
		bool $returnType = true
	) : bool
	{
		return $this->auth->requestPassword( $username, $email, $returnType );
	}

	public function logout ( bool $returnType = true ) : bool
	{
		return $this->auth->logout( $returnType );
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
}


class User implements UserInterface
{
	public function create ( array $data ) : bool
	{
		return true;
	}
}


class Mail implements MailInterface
{
	public function to ( $to ) : self
	{
		return $this;
	}

	public function subject ( $subject ) : self
	{
		return $this;
	}

	public function send () : bool
	{
		return true;
	}
}