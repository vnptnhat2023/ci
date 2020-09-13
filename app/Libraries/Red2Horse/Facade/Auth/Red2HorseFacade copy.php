<?php

# --------------------------------------------------------------------------
# --- Todo: role,permission [ id, group, route, permission ]
# --- Todo: Session & cookie -Todo ]
# --------------------------------------------------------------------------

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\{
	Session\SessionFacade as session,
	Validation\ValidationFacade as validation,
	Cookie\CookieFacade as cookie,
	Cache\CacheFacade as cache,
	Mail\MailFacade as mail,
	Request\RequestFacade as request,
	Database\ThrottleFacade as throttleModel,
	Database\UserFacade as userModel,
	Common\CommonFacade as common,
};

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

# --------------------------------------------------------------------------

class Red2HorseFacade
{
	use TraitSingleton;

	public Config $config;
	public Message $message;

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

	# ------------------------------------------------------------------------

	protected throttleModel $throttleModel;
	protected userModel $userModel;
	protected session $session;
	protected cookie $cookie;
	protected validation $validation;
	protected cache $cache;
	protected mail $mail;
	protected request $request;
	protected common $common;

	# ------------------------------------------------------------------------

	public function __construct ( Config $config = null )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->cache()
		->common()
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

		$this->message = Message::getInstance( $config );
	}

	# ------------------------------------------------------------------------

	public function login (
		string $userNameEmail = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	) : bool
	{
		/**
		 * Authentication-Todo
		 * Props: [ username, password, rememberMe, captcha]
		 * Method: Utility->typeChecker( 'login' )
		 */
		$this->username = $userNameEmail;
		$this->password = $password;
		$this->rememberMe = (bool) $rememberMe;
		$this->captcha = $captcha;

		return $this->typeChecker( 'login' );
	}

	public function logout () : bool
	{
		/**
		 * Authentication-Todo
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

	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool
	{
		/**
		 * ResetPassword-Todo
		 * Props: [ username, email, captcha ]
		 * Methods: Utility->typeChecker( 'forget' )
		 */
		$this->username = $username;
		$this->email = $email;
		$this->captcha = $captcha;

		return $this->typeChecker( 'forget' );
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		/**
		 * Authentication-Todo
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

	public function getHashPass ( string $password ) : string
  {
		/**
		 * Password-Todo
		 */
		$hash = password_hash(
			base64_encode( hash( 'sha384', $password, true ) ),
			PASSWORD_DEFAULT
		);

		return $hash;
  }

  public function getVerifyPass ( string $password, string $hashed ) : bool
  {
		/**
		 * Password-Todo
		 */
		$result = password_verify(
			base64_encode( hash( 'sha384', $password, true ) ),
			$hashed
		);

		return $result;
	}

	/**
	 * Depend on static property $returnType
	 * @return object|array
	 */
	public function getResult ()
	{
		return $this->message->getResult();
	}

	/**
	 * Receive all types of messages in this class
	 * @return array|object
	 */
	public function getMessage ( array $addMore = [], bool $asObject = true )
	{
		$form = [
			'form' => [
				'username' => $this->username,
				'email' => $this->email,
				'password' => $this->password,
				'captcha' => $this->captcha,
				'remember_me' => $this->rememberMe
			]
		];

		if ( ! empty( $addMore ) ) {
			$form = array_merge( $form, $addMore );
		}

		return $this->message->getMessage( $form, $asObject );
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
		/**
		 * Authentication-Todo
		 * Components: [ config, session ]
		 * Methods: [ Cookie->cookieHandler() ]
		 */
		if ( true === $this->session->has( $this->config->session ) ) {
			return true;
		}

		return ( false === $withCookie ) ? false : $this->cookieHandler();
	}

	public function regenerateSession ( array $userData ) : bool
	{
		/**
		 * Session-Todo
		 * Components: [ common, userModel ]
		 * Methods: [ Authentication->isLogged(), Cookie->regenerateCookie() ]
		 */
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

	public function regenerateCookie () : void
	{
		/**
		 * Cookie-Todo
		 * Components: [ config, cookie ]
		 */
		$cookieValue = password_hash( session_id(), PASSWORD_DEFAULT );
		$ttl = $this->config->sessionTimeToUpdate;
		$cookieName = $this->config->cookie;

		$this->cookie->set_cookie( $cookieName . '_test', $cookieValue, $ttl );
	}

	# ------------------------------------------------------------------------

	private function cookieHandler () : bool
	{
		/**
		 * Cookie-Todo
		 * Components: [ config, cookie, message, session, userModel, request ]
		 * Methods: [
		 * Authentication->isMultiLogin( $user[ 'session_id' ] )
		 * Cookie->setCookie( $user[ 'id' ], [], $logErr )
		 * Cookie->regenerateCookie()
		 * ]
		 */
		$userCookie = $this->cookie->get_cookie( $this->config->cookie );

		if ( empty( $userCookie ) || ! is_string( $userCookie ) ) {
			return false;
		}

		$separate = explode( ':', $userCookie, 2 );
		$incorrectCookie = function  () : bool {
			$this->cookie->delete_cookie( $this->config->cookie );
			return false;
		};

		if ( empty( $separate[ 0 ] ) || empty( $separate[ 1 ] ) ) {
			return $incorrectCookie();
		}

		$selector = $separate[ 0 ];
		$token = $separate[ 1 ];

		$user = $this->userModel->getUserWithGroup(
			$this->config->getColumString(),
			[ 'selector' => $selector ]
		);

		if ( empty( $user ) ) {
			return $incorrectCookie();
		}

		$isValid = hash_equals( $user[ 'token' ], hash( 'sha256', $token ) );
		$isUserIp = $user[ 'last_login' ] == $this->request->getIPAddress();

		if ( false === $isValid || false === $isUserIp ) {
			return $incorrectCookie();
		}

		# --- Check status
		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) ) {
			$this->message->denyStatus( $user[ 'status' ], false, false );
			return $incorrectCookie();
		}

		# --- Todo: declare inside the config file: is using this feature
		if ( false === $this->isMultiLogin( $user[ 'session_id' ] ) ) {
			$this->message->denyMultiLogin( true, [], false );
			return false;
		}

		# --- refresh new cookie
		$logErr = "Validated cookie, but error when update userId: {$user[ 'id' ]}";
		$this->setCookie( $user[ 'id' ], [], $logErr );

		$user[ 'permission' ] = json_decode( $user[ 'permission' ] );
		$this->session->set( $this->config->session, $user );

		$this->regenerateCookie();

		return true;
	}

	private function loginInvalid ()
	{
		/**
		 * Authentication-Todo
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
		 * Authentication-Todo
		 * Props: [ username, password ]
		 * Methods: [
		 * Authentication->getVerifyPass( $this->password, $userData[ 'password' ] )
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

		$verifyPassword = $this->getVerifyPass(
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

	private function loginHandler () : bool
	{
		/**
		 * Authentication-Todo
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

	private function setLoggedInSuccess ( array $userData ) : void
	{
		/**
		 * Authentication-Todo
		 * Components: [ common, message ]
		 */
		$this->message->successfully = true;
		$this->message->success[] = $this->common->lang(
			'Red2Horse.successLoggedWithUsername',
			[ $userData[ 'username' ] ]
		);
	}

	/**
	 * Validation form forgot password and send mail
	 */
	private function forgetHandler () : bool
	{
		/**
		 * ResetPassword-Todo
		 * Props: [ username, email ]
		 * Components: [ config, common, message, userModel, validation, throttleModel ]
		 * Methods: [
		 * Password->getHashPass( $this->common->random_string() )
		 * Notification->mailSender( $this->common->random_string() )
		 * ]
		 */
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
			$this->message->incorrectInfo( true, array_values( $validation->getErrors() ) );

			return false;
		}

		$find_user = $this->userModel->getUser( $this->config->getColumString() ,$data );

		if ( empty( $find_user ) ) {
			$this->message->incorrectInfo();

			return false;
		}

		$randomPw = $this->common->random_string();
		$hashPw = $this->getHashPass( $randomPw );

		$updatePassword = $this->userModel->updateUser(
			[ 'username' => $find_user[ 'username' ] ],
			[ 'password' => $hashPw ]
		);

		$error = 'The system is busy, please come back later';

		if ( false === $updatePassword ) {
			$this->message->errors[] = $error;

			return false;
		}

		if ( ! $this->mailSender( $randomPw ) ) {

			$this->message->errors[] = $error;

			$this->common->log_message(
				'error' ,
				"Cannot sent email: {$find_user[ 'username' ]}"
			);

			return false;
		}

		$this->message->successfully = true;
		$this->message->success[] = $this->common->lang( 'Red2Horse.successResetPassword' );

		return true;
	}

	private function isMultiLogin ( string $session_id ) : bool
	{
		/**
		 * Authentication-Todo
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

	# --- Todo: and Exception
	private function setCookie ( int $userId, array $updateData = [], string $logError = null ) : void
	{
		/**
		 * Cookie-Todo
		 * Components: [ config, common ]
		 * Methods: [ Authentication->loggedInUpdateData( $userId, $data ) ]
		 */
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception( $this->common->lang( 'Validation.greater_than', $errArg ), 1 );
		}

		if ( ! empty( $updateData ) && false === $this->common->isAssocArray( $updateData ) ) {
			throw new \Exception( $this->common->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$selector = bin2hex( random_bytes( 8 ) );
		$token = bin2hex( random_bytes( 20 ) );

		$cookieValue = "{$selector}:{$token}";
		$data = [
			'selector' => $selector,
			'token' => hash( 'sha256', $token )
		];
		$data = array_merge( $data, $updateData );

 		if ( true === $this->loggedInUpdateData( $userId, $data ) )
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

	/**
	 * @param string $type login|forget
	 * @throws \Exception
	 */
	private function typeChecker ( $type = 'login' ) : bool
	{
		/**
		 * Utility-Todo
		 * Props: [ username, password, email ]
		 * Components: [ config, common, message, throttleModel ]
		 * Methods: [
		 * Authentication->isLogged( true )
		 * Authentication->setLoggedInSuccess( $this->getUserdata() )
		 * Authentication->getUserdata()
		 * Authentication->loginHandler()
		 * ResetPassword->forgetHandler()
		 * ]
		 */
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
			? $this->message->incorrectResetPassword = true
			: $this->setLoggedInSuccess( $this->getUserdata() );

			return true;
		}

		if ( true === $this->throttleModel->limited() )
		{
			$errArg = [
				'num' => gmdate( 'i', $this->config->throttle->timeoutAttempts ),
				'type' => 'minutes'
			];
			$this->message->errors[] = $this->common->lang( 'Red2Horse.errorThrottleLimitedTime', $errArg );

			return false;
		}

		if ( false === $hasRequest ) return false;

		return ( $type === 'login' ) ? $this->loginHandler() : $this->forgetHandler();
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	private function loggedInUpdateData ( int $userId, array $updateData = [] )
	{
		/**
		 * Authentication-Todo
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

	private function mailSender ( string $randomPw ) : bool
	{
		/**
		 * Notification-Todo
		 * Components: [ mail ]
		 */
		$this->mail
		// ->setFrom ( 'localhost@example.com', 'Administrator' )
		->to ( 'exa@example.com' )
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