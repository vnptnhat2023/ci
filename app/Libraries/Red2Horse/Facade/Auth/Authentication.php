<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;
use Red2Horse\Facade\{
	Session\SessionFacadeInterface as session,
	Validation\ValidationFacadeInterface as validation,
	Cookie\CookieFacadeInterface as cookie,
	Request\RequestFacadeInterface as request,
	Database\ThrottleFacadeInterface as throttleModel,
	Database\UserFacadeInterface as userModel,
	Common\CommonFacadeInterface as common,
	Event\EventFacadeInterface as event
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
	protected event $event;

	private static ?string $username = null;
	private static ?string $password = null;
	private static ?string $captcha = null;
	private static bool $rememberMe = false;

	public function __construct( Config $config )
	{
		$this->config = $config;
		$this->message = Message::getInstance( $config );
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
		->event()
		->build();

		$this->common = $builder->common;
		$this->cookie = $builder->cookie;
		$this->session = $builder->session;
		$this->validation = $builder->validation;
		$this->request = $builder->request;
		$this->userModel = $builder->user;
		$this->throttleModel = $builder->throttle;
		$this->event = $builder->event;
	}

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool
	{
		self::$username = $u;
		self::$password = $p;
		self::$rememberMe = $r;
		self::$captcha = $c;

		return $this->utility->typeChecker( 'login', $u, $p, null, $c );
	}

	public function logout () : bool
	{
		$this->message::$successfully = true;
		$this->cookie->delete_cookie( $this->config->cookie );

		if ( $this->session->has( $this->config->session ) ) {
			$this->session->destroy();
			$this->message::$success[] = $this->common->lang( 'Red2Horse.successLogout' );

			return true;
		}

		$error = $this->common->lang( 'Red2Horse.errorNeedLoggedIn').  $this->common->lang( 'Red2Horse.homeLink');
		$this->message::$errors[] = $error;

		return false;
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( false === $this->isLogged() ) {
			return false;
		}

		if ( empty( $key ) ) {
			return $this->session->get( $this->config->session );
		}

		$userData = $this->session->get( $this->config->session );

		return $userData[ $key ] ?? null;
	}

	/**
	 * Check cookie, session: when have cookie will set session
	 * @return boolean
	 */
	public function isLogged ( bool $withCookie = false ) : bool
	{
		if ( true === $this->session->has( $this->config->session ) ) {
			return true;
		}

		return ( false === $withCookie )
		? false
		: $this->cookieHandle->cookieHandler();
	}

	private function loginInvalid ()
	{
		$validation = $this->validation;
		$config = $this->config;

		if ( true === $this->throttleModel->showCaptcha() ) {

			$data = [
				$config::USERNAME => self::$username,
				$config::PASSWORD => self::$password,
				$config::CAPTCHA => self::$captcha
			];

			$ruleCaptcha = [
				$config::CAPTCHA => $validation->getRules( $config::CAPTCHA )
			];

			if ( false === $validation->isValid( $data, $ruleCaptcha ) ) {
				$errorCaptcha = $validation->getErrors( $config::CAPTCHA );

				return $this->message->incorrectInfo( true, $errorCaptcha );
			}
		}

		$incorrectInfo = false;

		$ruleUsername = [
			$config::USERNAME => $validation->getRules( 'username' )
		];

		$data = [ $config::USERNAME => self::$username ];

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
		$userDataArgs = [
			'user.username' => self::$username,
			'user.email' => self::$username
		];

		$userData = $this->userModel->getUserWithGroup(
			$this->config->getColumString( [ 'password' ] ),
			$userDataArgs
		);

		if ( empty( $userData ) ) {
			return [ 'error' => $this->message->incorrectInfo() ];
		}

		$verifyPassword = Password::getInstance()->getVerifyPass(
			self::$password, $userData[ 'password' ]
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

		$isValidJson = $this->common->valid_json( $userData[ 'permission' ] );

		$userData[ 'permission' ] = ( true === $isValidJson )
		? json_decode( $userData[ 'permission' ], true )
		: [];

		return $userData;
	}

	public function loginHandler () : bool
	{
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
		$userId = (int) $userData[ 'id' ];

		if ( true === self::$rememberMe )
		{
			$this->cookieHandle->setCookie( $userId );
		}
		else if ( false === $this->loggedInUpdateData( $userId ) )
		{
			$this->common->log_message( 'error', "{$userId} Logged-in, but update failed" );
		}

		$this->cookieHandle->regenerateCookie();
		# --- End cookie set

		# --- Clean old throttle attempts
		$this->throttleModel->cleanup();

		return true;
	}

	public function setLoggedInSuccess ( array $userData ) : void
	{
		$this->message::$successfully = true;

		$this->message::$success[] = $this->common->lang(
			'Red2Horse.successLoggedWithUsername',
			[ $userData[ 'username' ] ]
		);
	}

	public function isMultiLogin ( ?string $session_id = null ) : bool
	{
		if ( ! $this->config->useMultiLogin ) {
			return true;
		}

		$pathFile = $this->config->sessionSavePath;
		$pathFile .= '/' . $this->config->sessionCookieName . $session_id;
		$date = $this->common->get_file_info( $pathFile, 'date' );

		if ( empty( $date ) ) { return true; }

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

		$this->message::$errors[] = 'else';

		return false;
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	public function loggedInUpdateData ( int $userId, array $updateData = [] )
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];

			throw new \Exception(
				$this->common->lang( 'Validation.greater_than', $errArg ),
				1
			);
		}

		$isAssocData = $this->common->isAssocArray( $updateData );

		if ( ! empty( $updateData ) && !$isAssocData ) {
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