<?php

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\{
	Session\SessionFacadeInterface as session,
	Validation\ValidationFacadeInterface as validation,
	Cookie\CookieFacadeInterface as cookie,
	Cache\CacheFacadeInterface as cache,
	Mail\MailFacadeInterface as mail,
	Request\RequestFacadeInterface as request,
	Database\ThrottleFacadeInterface as throttleModel,
	Database\UserFacadeInterface as userModel,
	Common\CommonFacadeInterface as common,
};

class Red2HorseFacade
{
	public Config $config;

	# --- Result data
	protected bool $incorrectResetPassword = false;
	protected bool $incorrectLoggedIn = false;
	protected bool $successfully = false;
	protected bool $hasBanned = false;
	protected bool $accountInactive = false;

	# --- Form data
	/**
	 * @var string $username form-data
	 */
	private ?string $username = null;
	/**
	 * @var string $email form-data
	 */
	private ?string $email = null;
	/**
	 * @var string $password form-data
	 */
	private ?string $password = null;
	/**
	 * @var string $captcha form-data
	 */
	private ?string $captcha = null;
	/**
	 * @var bool $rememberMe form-data
	 */
	private bool $rememberMe = false;

	# --- Message data
	protected array $errors = [];
	protected array $success = [];

	protected throttleModel $throttleModel;
	protected userModel $userModel;
	protected session $session;
	protected cookie $cookie;
	protected validation $validation;
	protected cache $cache;
	protected mail $mail;
	protected request $request;
	protected common $common;

	public function __construct ( Config $config = null )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->cache()
		->common()
		->config()
		->cookie()
		->database_user()
		->database_throttle()
		->mail()
		->request()
		->session()
		->validation()
		->build();

		$this->throttleModel = $builder->throttle;
		$this->userModel = $builder->user;
		$this->session = $builder->session;
		$this->cookie = $builder->cookie;
		$this->validation = $builder->validation;
		$this->cache = $builder->cache;
		$this->mail = $builder->mail;
		$this->request = $builder->request;
		$this->common = $builder->common;

		$this->throttleModel->config(
			$config->throttle->type,
			$config->throttle->captchaAttempts,
			$config->throttle->maxAttempts,
			$config->throttle->timeoutAttempts
		);
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
		$this->rememberMe = (bool) $rememberMe;
		$this->captcha = $captcha;

		return $this->typeChecker( 'login' );
	}

	public function logout () : bool
	{
		$this->successfully = true;

		$this->cookie->delete_cookie( $this->config->cookie );

		if ( $this->session->has( $this->config->session ) )
		{
			$this->session->destroy();
			$this->success[] = $this->common->lang( 'Red2Horse.successLogout' );

			return true;
		}

		$error = 'You have not login. '.  $this->common->lang( 'Red2Horse.homeLink');
		$this->errors[] = $error;

		return false;
	}

	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool
	{
		$this->username = $username;
		$this->email = $email;
		$this->captcha = $captcha;

		return $this->typeChecker( 'forget' );
	}

	/**
	 * @param string $type login|forget
	 * @throws \Exception
	 */
	private function typeChecker ( $type = 'login' ) : bool
	{
		if ( ! in_array( $type, [ 'login', 'forget' ] ) ) {
			throw new \Exception( 'Type must be in "login or forget"', 1 );
		}

		$requestType = ( $type === 'login' ) ? 'password' : 'email';
		$isNullUsername = is_null( $this->username );
		$isNullType = is_null( $this->{$requestType} );

		$hasRequest = ! $isNullUsername && ! $isNullType;

		if ( true === $this->isLogged( true ) )
		{
			( $type === 'forget' )
			? $this->incorrectResetPassword = true
			: $this->setLoggedInSuccess( $this->getUserdata() );

			return true;
		}

		if ( true === $this->throttleModel->limited() )
		{
			$errArg = [
				'num' => gmdate( 'i', $this->config->throttle->timeoutAttempts ),
				'type' => 'minutes'
			];
			$this->errors[] = $this->common->lang( 'Red2Horse.errorThrottleLimitedTime', $errArg );

			return false;
		}

		if ( false === $hasRequest ) return false;

		return ( $type === 'login' ) ? $this->loginHandler() : $this->forgetHandler();
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( false === $this->isLogged() )
		return false;

		if ( empty( $key ) )
		return $this->session->get( $this->config->session );

		$userData = $this->session->get( $this->config->session );
		return $userData[ $key ] ?? null;
	}

	public function getHashPass ( string $pass, int $cost = 12 ) : string
  {
    return password_hash( $pass, PASSWORD_BCRYPT, [ 'cost' => $cost ] );
  }

  public function getVerifyPass (
		string $password, string $salt
	) : bool
  {
  	return password_verify( $password, $salt );
	}

	/**
	 * Depend on static property $returnType
	 * @return object|array
	 */
	public function getResult ()
	{
		return [
			'incorrectResetPassword' => $this->incorrectResetPassword,
			'incorrectLoggedIn' => $this->incorrectLoggedIn,
			'successfully' => $this->successfully,
			'hasBanned' => $this->hasBanned,
			'accountInactive' => $this->accountInactive,
			'attempt' => $this->throttleModel->getAttempts(),
			'showCaptcha' => $this->throttleModel->showCaptcha()
		];
	}

	/**
	 * Receive all types of messages in this class
	 * @return array|object
	 */
	public function getMessage ( array $addMore = [], bool $asObject = true )
	{
		$sysCaptcha = $this->session->getFlashdata( $this->config::CAPTCHA );

		$message = [
			'success' => $this->success,
			'errors' => $this->errors,
			'result' => $this->getResult(),
			'form' => [
				'username' => $this->username,
				'email' => $this->email,
				'password' => $this->password,
				'captcha' => $this->captcha,
				'remember_me' => $this->rememberMe
			],
			'r2h_auth' => [
				'config' => $this->config,
				'captcha' => $sysCaptcha[ 'word' ] ?? null
			]
		];

		empty( $addMore ) ?: $message += $addMore;

		return ( true === $asObject )
		? json_decode( json_encode( $message ) )
		: $message;
	}

	/**
	 * The first check the current user session, * the next will be $data parameter
	 * @param array $data case empty array ( [] ) = 1st group = administrator
	 * @return boolean
	 */
	public function hasPermission ( array $data ) : bool
	{
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

		# --- Todo: permission config
		$configPerm = config( '\BAPI\Config\User' )->setting( 'permission' );
		$boolVar = true;

		foreach ( $data as $role )
		{
			$inCfPerm = in_array( $role, $configPerm, true );
			$inUserPerm = in_array( $role, $userPerm, true );

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
		if ( true === $this->session->has( $this->config->session ) ) {
			return true;
		}

		return ( false === $withCookie ) ? false : $this->cookieHandler();
	}

	public function regenerateSession ( array $userData ) : bool
	{
		if ( false === $this->isLogged() )
		return false;

		$isUpdated = $this->userModel->updateUser(
			$userData[ 'id' ],
			[ 'session_id' => session_id() ]
		);

		if ( false === $isUpdated ) {
			$this->common->log_message( 'error', "The session_id of {$userData[ 'id' ]} update failed" );
			return false;
		}

		$this->regenerateCookie();
		return true;
	}

	private function cookieHandler () : bool
	{
		$userCookie = $this->cookie->get_cookie( $this->config->cookie );

		if ( empty( $userCookie ) || ! is_string( $userCookie ) )
		return false;

		$exp = explode( '-', $userCookie, 2 );

		$incorrectCookie = function  () : bool {
			$this->cookie->delete_cookie( $this->config->cookie );
			return false;
		};

		if ( empty( $exp[ 0 ] ) || empty( $exp[ 1 ] ) )
		return $incorrectCookie();

		$userId = hex2bin( $exp[ 1 ] );
		if ( $userId === '0' || ! ctype_digit( $userId ) )
		return $incorrectCookie();

		# Check token
		$user = $this->userModel->getUser( [ 'id' => $userId ] );
		if ( empty( $user ) )
		return $incorrectCookie();

		$userToken = password_verify( $user[ 'cookie_token' ], $exp[ 0 ] );

		$ip = $this->request->getIPAddress();
		$userIp = $user[ 'last_login' ] == $ip;

		if ( false === $userToken || false === $userIp )
		return $incorrectCookie();

		# Check status
		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) ) {
			$this->denyStatus( $user[ 'status' ], false, false );
			return $incorrectCookie();
		}

		if ( false === $this->isMultiLogin( $user[ 'session_id' ] ) ) {
			$this->denyMultiLogin( true, [], false );
			return false;
		}

		# --- Update cookie
		$logErr = "Cookie success checked, but error when update data: {$userId}";

		# --- Set cookie
		$this->setCookie( $userId, [], $logErr );

		# --- Create new session
		$userData = $this->userModel->getUserWithGroup( [ 'user.id' => $userId ] );
		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );
		$this->session->set( $this->config->session, $userData );
		$this->regenerateCookie();

		return true;
	}

	private function loginInvalid ()
	{
		$validation = $this->validation;
		$config = $this->config;

		if ( true === $this->throttleModel->showCaptcha() )
		{
			$ruleCaptcha = [
				$config::CAPTCHA => $validation->getRules( $config::CAPTCHA )
			];

			$data = [
				$config::USERNAME => $this->username,
				$config::PASSWORD => $this->password,
				$config::CAPTCHA => $this->captcha
			];

			if ( false === $validation->isValid( $data, $ruleCaptcha ) ) {
				$errorCaptcha = $validation->getErrors( $config::CAPTCHA );

				return $this->incorrectInfo( true, $errorCaptcha );
			}
		}

		$incorrectInfo = false;
		$ruleUsername = [
			$config::USERNAME => $validation->getRules( 'username' )
		];
		$data = [ $config::USERNAME => $this->username ];

		if ( false === $validation->isValid( $data, $ruleUsername ) )
		{
			$validation->reset();

			$ruleEmail = [
				$config::USERNAME => $validation->getRules( 'email' )
			];

			$incorrectInfo = ! $validation->isValid( $data, $ruleEmail );
		}

		false === $incorrectInfo ?: $this->incorrectInfo( true );

		return $incorrectInfo;
	}

	private function loginAfterValidation () : array
	{
		$userData = $this->userModel->getUserWithGroup(
			[ 'user.username' => $this->username ],
			[ 'password' ]
		);
		if ( empty( $userData ) ) {
			return [ 'error' => $this->incorrectInfo() ];
		}

		$verifyPassword = $this->getVerifyPass( $this->password, $userData[ 'password' ] );
		if ( false === $verifyPassword ) {
			return [ 'error' => $this->incorrectInfo() ];
		}

		if ( 'active' !== $userData[ 'status' ] ) {
			return [ 'error' => $this->denyStatus( $userData['status'] ) ];
		}

		if ( false === $this->isMultiLogin( $userData[ 'session_id' ] ) ) {
			$this->denyMultiLogin( true, [], false );

			return [ 'error' => false ];
		}

		unset( $userData[ 'password' ] );
		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );

		return $userData;
	}

	private function loginHandler () : bool
	{
		if ( false !== $this->loginInvalid() ) {
			return false;
		}

		$userData = $this->loginAfterValidation();
		if ( true === array_key_exists( 'error', $userData ) ) {
			return false;
		}
		# return $userData[ 'error' ] ?? $userData;

		# --- Set response success to true
		$this->setLoggedInSuccess( $userData );
		# --- Set session
		$this->session->set( $this->config->session, $userData );

		# --- Set cookie
		$userId = $userData[ 'id' ];
		if ( true === $this->rememberMe )
		{
			$this->setCookie( $userId );
		}
		else if ( false === $this->loggedInUpdateData( $userId ) )
		{
			$this->common->log_message( 'error', "{$userId} Logged-in, but update failed" );
		}

		$this->regenerateCookie();
		$this->throttleModel->cleanup();

		return true;
	}

	/**
	 * Validation form forgot password and send mail
	 */
	private function forgetHandler () : bool
	{
		$validation = $this->validation;

		$group = ( true === $this->throttleModel->showCaptcha() )
		? $this->config::FORGET_WITH_CAPTCHA
		: $this->config::FORGET;

		$rules = $validation->getRules( $this->config->ruleGroup[ $group ] );

		$data = [
			$this->config::USERNAME => $this->username,
			$this->config::EMAIL => $this->email
		];

		if ( false === $validation->isValid( $data, $rules ) ) {
			$this->incorrectInfo( true, array_values( $validation->getErrors() ) );

			return false;
		}

		$find_user = $this->userModel->getUser( $data );

		if ( empty( $find_user ) ) {
			$this->incorrectInfo();

			return false;
		}

		$randomPw = random_string();
		$hashPw = $this->getHashPass( $randomPw );

		$updatePassword = $this->userModel->updateUser(
			[ 'username' => $find_user[ 'username' ] ],
			[ 'password' => $hashPw ]
		);

		$error = 'The system is busy, please come back later';

		if ( false === $updatePassword ) {
			$this->errors[] = $error;

			return false;
		}

		if ( ! $this->mailSender( $randomPw ) ) {

			$this->errors[] = $error;
			$this->common->log_message(
				'error' ,
				"Cannot sent email: {$find_user[ 'username' ]}"
			);

			return false;
		}

		$this->successfully = true;
		$this->success[] = $this->common->lang( 'Red2Horse.successResetPassword' );

		return true;
	}

	# --- Todo: clone $config->sessionSavePath to r2hConfig ?
	private function isMultiLogin ( string $session_id ) : bool
	{
		// $config = config( '\Config\App' );
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

		$this->errors[] = "else";
		return false;
	}

	/** @read_more getMessage */
	private function denyMultiLogin (
		bool $throttle = true, array $addMore = [], $getReturn = true
	)
	{
		false === $throttle ?: $this->throttleModel->throttle();
		$this->incorrectLoggedIn = true;

		$errors[] = $this->common->lang( 'Red2Horse.noteLoggedInAnotherPlatform' );
		$this->errors = [ ...$errors, ...array_values( $addMore ) ];

		if ( true === $getReturn ) return $this->getMessage();
	}

	/** @read_more getMessage */
	private function incorrectInfo ( bool $throttle = true, array $addMore = [] )
	{
		false === $throttle ?: $this->throttleModel->throttle();
		$this->incorrectLoggedIn = true;

		$errors[] = $this->common->lang( 'Red2Horse.errorIncorrectInformation' );
		$this->errors = [ ...$errors, ...array_values( $addMore ) ];

		return $this->getMessage();
	}

	/**
	 * @return object|array|void
	 */
	private function denyStatus (
		string $status,
		bool $throttle = true,
		$getReturn = true
	)
	{
		false === $throttle ?: $this->throttleModel->throttle();
		$this->hasBanned = $status === 'banned';
		$this->accountInactive = $status === 'inactive';
		$this->errors[] = $this->common->lang( 'Red2Horse.errorNotReadyYet', [ $status ] );

		if ( true === $getReturn ) return $this->getMessage();
	}

	# --- Todo: and Exception
	private function setCookie ( int $userId, array $data = [], string $logError = null ) : void
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception( $this->common->lang( 'Validation.greater_than', $errArg ), 1 );
		}

		if ( ! empty( $data ) && false === isAssoc( $data ) ) {
			throw new \Exception( $this->common->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$randomKey = random_bytes( $this->config->randomBytesLength );
		$idHex = bin2hex( $userId );
		$keyHex = bin2hex( $randomKey );
		$keyHash = password_hash( $keyHex, PASSWORD_DEFAULT );
		$cookieValue = "{$keyHash}-{$idHex}";

		$updateData = [ 'cookie_token' => $keyHex ];
		$updateData += $data;

 		if ( true === $this->loggedInUpdateData( $userId, $updateData ) )
		{
			$ttl = time() + $this->config->ttl;
			setcookie( $this->config->cookie, $cookieValue, $ttl, '/' );
		}
		else
		{
			$logErr = $logError ?: "{$userId} LoggedIn with remember-me, but update failed";
			$this->common->log_message( 'error', $logErr );
		}
	}

	private function setLoggedInSuccess ( array $userData ) : void
	{
		$this->successfully = true;
		$this->success[] = $this->common->lang( 'Red2Horse.successLoggedWithUsername', [ $userData[ 'username' ] ] );
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	private function loggedInUpdateData ( int $userId, array $data = [] )
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];

			throw new \Exception( $this->common->lang( 'Validation.greater_than', $errArg ), 1 );
		}

		if ( ! empty( $data ) && false === isAssoc( $data ) ) {
			throw new \Exception( $this->common->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$ssId = session_id();
		$updateData = [
			'last_login' => $this->request->getIPAddress(),
			'last_activity' => date( 'Y-m-d H:i:s' ),
			'session_id' => $ssId
		];

		$updateData += $data;

		return $this->userModel->updateUser( $userId, $updateData );
	}

	public function regenerateCookie () : void
	{
		// $config = config( '\Config\App' );
		$cookieValue = password_hash( session_id(), PASSWORD_DEFAULT );
		$ttl = $this->config->sessionTimeToUpdate;
		// $cookieName = $config->sessionCookieName;
		$cookieName = $this->config->cookie;

		// setcookie( $cookieName . '_test', $cookieValue, $ttl, '/' );
		$this->cookie->set_cookie( $cookieName . '_test', $cookieValue, $ttl );
	}

	private function trigger ( string $event, array $eventData )
	{
		if ( ! isset( $this->{$event} ) || empty( $this->{$event} ) ) return $eventData;

		foreach ( $this->{$event} as $callback )
		{
			if ( ! method_exists( $this, $callback ) ) {
				throw new \Exception( 'forInvalidMethodTriggered', 403 );
			}

			$eventData = $this->{$callback}( $eventData );
		}

		return $eventData;
	}

	private function mailSender ( string $randomPw ) : bool
	{
		$this->mail
		// ->setFrom ( 'localhost@example.com', 'Administrator' )
		->to ( 'cukikt0302@gmail.com' )
		// ->setCC ( 'another@another-example.com' )
		// ->setBCC ( 'them@their-example.com' )
		->subject ( 'Email Test' )
		->message ( 'your password has been reset to: ' . $randomPw );

		if ( false === $this->mail->send() ) {
			throw new \Exception( $this->mail->getErrors(), 403 );
		}

		return true;
	}
}