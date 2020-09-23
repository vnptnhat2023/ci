<?php

# --------------------------------------------------------------------------
# --- Todo: [ permission: 'all', null === empty, c, r, u, d ] => [ route ]
# --- Case: route[ get, post, delete, put, patch , ... ??? ]
# --- Case: c => [ page, post, ... ]
# --- Case: c => [ 'all' ] || []
# --------------------------------------------------------------------------
declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\Database\ThrottleFacade as throttleModel;
use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class Red2Horse
{
	use TraitSingleton;

	public Config $config;
	protected throttleModel $throttleModel;
	protected Authentication $authentication;
	protected Message $message;

	# ------------------------------------------------------------------------

	public function __construct ( Config $config = null )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->common()
		->database_throttle()
		->build();

		$this->throttleModel = $builder->throttle;

		$this->throttleModel->config(
			$config->throttle->type,
			$config->throttle->captchaAttempts,
			$config->throttle->maxAttempts,
			$config->throttle->timeoutAttempts
		);

		$this->authentication = Authentication::getInstance( $this->config );
		$this->message = Message::getInstance( $this->config );
	}

	# ------------------------------------------------------------------------

	public function login
	(
		string $userNameEmail = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	) : bool
	{
		return $this->authentication->login(
			$userNameEmail,
			$password,
			$rememberMe,
			$captcha
		);
	}

	public function logout () : bool
	{
		return $this->authentication->logout();
	}

	public function requestPassword
	(
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool
	{
		return ResetPassword::getInstance( $this->config )
		->requestPassword( $username, $email, $captcha );
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		return $this->authentication->getUserdata( $key );
	}

	public function getHashPass ( string $password ) : string
  {
		return Password::getInstance()->getHashPass( $password );
  }

  public function getVerifyPass ( string $password, string $hashed ) : bool
  {
		return Password::getInstance()->getVerifyPass( $password, $hashed );
	}

	/** @return object|array */
	public function getResult ()
	{
		return $this->message->getResult();
	}

 	public function getMessage ( array $addMore = [], bool $asObject = true )
	{
		return $this->message->getMessage( $addMore, $asObject );
	}

	public function withPermission ( array $data ) : bool
	{
		return Authorization::getInstance( $this->config )
		->withPermission( $data );
		// return Authorization::getInstance( $this->config )
		// ->withGroup( $data );
	}

	public function withRole ( string $role ) : bool
	{
		return Authorization::getInstance( $this->config )
		->withRole( $role );
	}

	public function isLogged ( bool $withCookie = false ) : bool
	{
		return $this->authentication->isLogged( $withCookie );
	}

	public function regenerateSession ( array $userData ) : bool
	{
		return SessionHandle::getInstance( $this->config )
		->regenerateSession( $userData );
	}

	public function regenerateCookie () : void
	{
		CookieHandle::getInstance( $this->config )->regenerateCookie();
	}
}