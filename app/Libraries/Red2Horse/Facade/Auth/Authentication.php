<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

use App\Libraries\Red2Horse\Facade\{
	Session\SessionFacade as session,
	Validation\ValidationFacade as validation,
	Cookie\CookieFacade as cookie,
	Request\RequestFacade as request,
	Database\ThrottleFacade as throttleModel,
	Database\UserFacade as userModel,
	Common\CommonFacade as common,
};

class Authentication
{
	use TraitSingleton;

	protected Config $config;
	protected Message $message;
	protected CookieHandle $cookieHandle;
	protected Utility $utility;

	protected throttleModel $throttleModel;
	protected userModel $userModel;
	protected session $session;
	protected cookie $cookie;
	protected validation $validation;
	protected request $request;
	protected common $common;

	private ?string $username = null;
	private ?string $password = null;
	private ?string $captcha = null;
	private bool $rememberMe = false;

	public function __construct( Config $config )
	{
		$this->config = $config;
		$this->message = Message::getInstance();
		$this->cookieHandle = CookieHandle::getInstance( $config );
		$this->utility = Utility::getInstance( $config );

		$builder = AuthComponentBuilder::createBuilder( $config )
		->common()
		->cookie()
		->session()
		->validation()
		->request()
		->database_user()
		->database_throttle()
		->build();

		$this->common = $builder->common;
		$this->cookie = $builder->cookie;
		$this->session = $builder->session;
		$this->validation = $builder->validation;
		$this->request = $builder->request;
		$this->userModel = $builder->user;
		$this->throttleModel = $builder->throttle;
	}

	public function login (
		string $userNameEmail = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	) : bool
	{
		$this->username = $userNameEmail;
		$this->password = $password;
		$this->rememberMe = $rememberMe;
		$this->captcha = $captcha;

		return $this->utility->typeChecker(
			'login', $userNameEmail, $password, null, $captcha
		);
	}

	public function logout () : bool
	{
		/**
		 * Components: [ common, message, cookie, session ]
		 */
		$this->message->successfully = true;

		$this->cookie->delete_cookie( $this->config->cookie );

		if ( $this->session->has( $this->config->session ) )
		{
			$this->session->destroy();
			$this->message->success[] = $this->common->lang( 'Red2Horse.successLogout' );

			return true;
		}

		$error = 'You have not login. '.  $this->common->lang( 'Red2Horse.homeLink');
		$this->message->errors[] = $error;

		return false;
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		/**
		 * Components: [ config, session ]
		 * Methods: [ Authentication->isLogged() ]
		 */
		if ( false === $this->isLogged() )
		return false;

		if ( empty( $key ) )
		return $this->session->get( $this->config->session );

		$userData = $this->session->get( $this->config->session );
		return $userData[ $key ] ?? null;
	}

	/**
	 * Check cookie, session: when have cookie will set session
	 * @return boolean
	 */
	public function isLogged ( bool $withCookie = false ) : bool
	{
		/**
		 * Components: [ config, session ]
		 * Methods: [ Cookie->cookieHandler() ]
		 */
		if ( true === $this->session->has( $this->config->session ) ) {
			return true;
		}

		return ( false === $withCookie ) ? false : $this->cookieHandle->cookieHandler();
	}

	private function loginInvalid ()
	{
		/**
		 * Props: [ username, password, captcha ]
		 * Components: [ validation, config, throttleModel, message ]
		 */
		$validation = $this->validation;
		$config = $this->config;

		if ( true === $this->throttleModel->showCaptcha() ) {
			$ruleCaptcha = [ $config::CAPTCHA => $validation->getRules( $config::CAPTCHA ) ];

			$data = [
				$config::USERNAME => $this->username,
				$config::PASSWORD => $this->password,
				$config::CAPTCHA => $this->captcha
			];

			if ( false === $validation->isValid( $data, $ruleCaptcha ) ) {
				$errorCaptcha = $validation->getErrors( $config::CAPTCHA );

				return $this->message->incorrectInfo( true, $errorCaptcha );
			}
		}

		$incorrectInfo = false;
		$ruleUsername = [ $config::USERNAME => $validation->getRules( 'username' ) ];
		$data = [ $config::USERNAME => $this->username ];

		if ( false === $validation->isValid( $data, $ruleUsername ) ) {
			$validation->reset();
			$ruleEmail = [ $config::USERNAME => $validation->getRules( 'email' ) ];
			$incorrectInfo = ! $validation->isValid( $data, $ruleEmail );
		}

		false === $incorrectInfo ?: $this->message->incorrectInfo( true );

		return $incorrectInfo;
	}

	private function loginAfterValidation () : array
	{
		/**
		 * Props: [ username, password ]
		 * Methods: [
		 * Password->getVerifyPass( $this->password, $userData[ 'password' ] )
		 * Authentication->isMultiLogin( $userData[ 'session_id' ] )
		 * ]
		 * Components: [ config, userModel, message ]
		 */
		$userDataArgs = [
			'user.username' => $this->username,
			'user.email' => $this->username
		];

		$userData = $this->userModel->getUserWithGroup(
			$this->config->getColumString( [ 'password' ] ),
			$userDataArgs
		);

		if ( empty( $userData ) ) {
			return [ 'error' => $this->message->incorrectInfo() ];
		}

		$verifyPassword = Password::getInstance()->getVerifyPass(
			$this->password, $userData[ 'password' ]
		);

		if ( false === $verifyPassword ) {
			return [ 'error' => $this->message->incorrectInfo() ];
		}

		if ( 'active' !== $userData[ 'status' ] ) {
			return [ 'error' => $this->message->denyStatus( $userData['status'] ) ];
		}

		if ( false === $this->isMultiLogin( $userData[ 'session_id' ] ) ) {
			$this->message->denyMultiLogin( true, [], false );

			return [ 'error' => false ];
		}

		unset( $userData[ 'password' ] );
		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );

		return $userData;
	}

	public function loginHandler () : bool
	{
		/**
		 * Props: [ rememberMe ]
		 *
		 * Methods: [
		 * Authentication->loginInvalid(),
		 * Authentication->loginAfterValidation(),
		 * Authentication->setLoggedInSuccess( $userData ),
		 * Cookie->setCookie( $userId ),
		 * Authentication->loggedInUpdateData( $userId ),
		 * Cookie->regenerateCookie()
		 * ]
		 *
		 * Components: [ config, common, throttleModel, session  ]
		 */
		if ( false !== $this->loginInvalid() ) {
			return false;
		}

		$userData = $this->loginAfterValidation();
		if ( true === array_key_exists( 'error', $userData ) ) {
			return false;
		}

		# --- Set response success to true
		$this->setLoggedInSuccess( $userData );

		# --- Set session
		$this->session->set( $this->config->session, $userData );

		# --- Set cookie
		$userId = $userData[ 'id' ];
		if ( true === $this->rememberMe )
		{
			$this->cookieHandle->setCookie( $userId );
		}
		else if ( false === $this->loggedInUpdateData( $userId ) )
		{
			$this->common->log_message( 'error', "{$userId} Logged-in, but update failed" );
		}

		$this->cookieHandle->regenerateCookie();
		$this->throttleModel->cleanup();

		return true;
	}

	public function setLoggedInSuccess ( array $userData ) : void
	{
		/**
		 * Components: [ common, message ]
		 */
		$this->message->successfully = true;
		$this->message->success[] = $this->common->lang(
			'Red2Horse.successLoggedWithUsername',
			[ $userData[ 'username' ] ]
		);
	}

	public function isMultiLogin ( string $session_id ) : bool
	{
		/**
		 * Components: [ config, common, cookie, message ]
		 */
		$pathFile = $this->config->sessionSavePath;
		$pathFile .= '/' . $this->config->sessionCookieName . $session_id;

		$date = $this->common->get_file_info( $pathFile, 'date' );
		if ( empty( $date ) ) {
			return true;
		}

		$cookieName = $this->config->sessionCookieName . '_test';
		if ( $hash = $this->cookie->get_cookie( $cookieName ) ) {

			if ( password_verify( $session_id, $hash ) ) {
				return true;
			}

			$this->cookie->delete_cookie( $cookieName );
			return false;
		}

		$time = ( time() - $date[ 'date' ] );
		$sessionExp = (int) $this->config->sessionExpiration;

		if ( $sessionExp > 0 ) {
			return $time < $sessionExp ? false : true;
		}

		if ( $sessionExp === 0 ) {
			return $time < $this->config->sessionTimeToUpdate ? false : true;
		}

		$this->message->errors[] = "else";
		return false;
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	public function loggedInUpdateData ( int $userId, array $updateData = [] )
	{
		/**
		 * Components: [ common, request, userModel ]
		 */
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];

			throw new \Exception( $this->common->lang( 'Validation.greater_than', $errArg ), 1 );
		}

		if ( ! empty( $updateData ) && false === $this->common->isAssocArray( $updateData ) ) {
			throw new \Exception( $this->common->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$data = [
			'last_login' => $this->request->getIPAddress(),
			'last_activity' => date( 'Y-m-d H:i:s' ),
			'session_id' => session_id()
		];

		$data = array_merge( $data, $updateData );

		return $this->userModel->updateUser( $userId, $data );
	}
}