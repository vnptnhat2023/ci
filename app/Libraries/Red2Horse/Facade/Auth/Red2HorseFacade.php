<?php

# --------------------------------------------------------------------------
# --- Todo: role,permission [ id, group, route, permission ]
# --------------------------------------------------------------------------

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\Database\ThrottleFacade as throttleModel;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

# --------------------------------------------------------------------------

class Red2HorseFacade
{
	use TraitSingleton;

	public Config $config;
	protected throttleModel $throttleModel;

	# ------------------------------------------------------------------------

	public function __construct ( Config $config = null )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->database_throttle()
		->build();

		$builder->throttle->config(
			$config->throttle->type,
			$config->throttle->captchaAttempts,
			$config->throttle->maxAttempts,
			$config->throttle->timeoutAttempts
		);
	}

	# ------------------------------------------------------------------------

	public function login (
		string $userNameEmail = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	) : bool
	{
		return Authentication::getInstance( $this->config )
		->login( $userNameEmail, $password, $rememberMe, $captcha );
	}

	public function logout () : bool
	{
		return Authentication::getInstance( $this->config )->logout();
	}

	public function requestPassword (
		string $username = null, string $email = null, string $captcha = null
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
		return Authentication::getInstance( $this->config )->getUserdata( $key );
	}

	public function getHashPass ( string $password ) : string
  {
		return Password::getInstance()->getHashPass( $password );
  }

  public function getVerifyPass ( string $password, string $hashed ) : bool
  {
		return Password::getInstance()->getVerifyPass( $password, $hashed );
	}

	/**
	 * @return object|array
	 */
	public function getResult ()
	{
		return Message::getInstance( $this->config )->getResult();
	}

	/**
	 * Receive all types of messages in this class
	 * ```
	 * $addMore = [
	 *		'form' => [
	 *			'username' => $_POST['username'],
	 *			'email' => $_POST['email'],
	 *			'password' => $_POST['password'],
	 *			'captcha' => $_POST['captcha'],
	 *			'remember_me' => $_POST['rememberMe']
	 *		]
	 * ];
	 * ```
	 *
	 * @return array|object
	 */
	public function getMessage ( array $addMore = [], bool $asObject = true )
	{
		return Message::getInstance( $this->config )->getMessage( $addMore, $asObject );
	}

	/**
	 * The first check the current user session, * the next will be $data parameter
	 * @param array $data case empty array ( [] ) = 1st group = administrator
	 * @return boolean
	 */
	public function hasPermission ( array $data ) : bool
	{
		/**
		 * Authorization
		 * Methods : [ getUserdata ]
		 * Components: [ config ]
		 */
		# --- Get current user permission
		$userPerm = $this->getUserdata( 'permission' );

		if ( ( false === $userPerm ) || empty( $userPerm ) )
		return false;

		if ( in_array( 'null', $userPerm, true ) )
		return false;

		if ( in_array( 'all', $userPerm, true ) )
		return true;

		# --- Administrator (1st) group !
		if ( empty( $data ) )
		return true;

		$userRoute = $this->config->userRoute;
		$boolVar = true;

		foreach ( $data as $route )
		{
			$inCfPerm = in_array( $route, $userRoute, true );
			$inUserPerm = in_array( $route, $userRoute, true );

			if ( false === $inCfPerm || false === $inUserPerm )
			{
				$boolVar = false;
				break;
			}
		}

		return $boolVar;
	}

	/**
	 * Check cookie, session: when have cookie will set session
	 * @return boolean
	 */
	public function isLogged ( bool $withCookie = false ) : bool
	{
		return Authentication::getInstance( $this->config )->isLogged( $withCookie );
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

	# ------------------------------------------------------------------------

	private function cookieHandler () : bool
	{
		return CookieHandle::getInstance( $this->config )->cookieHandler();
	}

	private function loginHandler () : bool
	{
		return Authentication::getInstance( $this->config )->loginHandler();
	}

	private function setLoggedInSuccess ( array $userData ) : void
	{
		Authentication::getInstance( $this->config )->setLoggedInSuccess( $userData );
	}

	private function forgetHandler (
		string $username = null, string $email = null, string $captcha = null
	) : bool
	{
		return ResetPassword::getInstance( $this->config )
		->forgetHandler( $username, $email, $captcha );
	}
}