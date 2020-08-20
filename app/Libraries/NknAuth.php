<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Encryption\Encryption;
use Config\Services;

class NknAuth
{
	/**
	 * @var NknAuth\Config
	 */
	public NknAuth\Config $config;

	protected array $response = [
		# --- When logged-in but request forget password
		'reset_incorrect' => false,
		'login_incorrect' => false,
		'success' => false,
		'view' => false,
		'captcha' => false,
		'limit_max' => false,
		# --- Times to show captcha, should change to: limited_level
		// 'was_limited_one' => false,
		'attemps' => 0,
		'banned' => false,
		'inactive' => false
	];

	protected array $messageErrors = [];
	protected array $messageSuccess = [];

	protected static string $returnType = 'object';

	/**
	 * @var \App\Models\Login $model
	 */
	protected \App\Models\Login $model;

	/**
	 * @var \CodeIgniter\Database\BaseBuilder $user
	 */
	public BaseBuilder $user;

	protected array $rules = [

		'login' => [ 'username', 'password' ],

		'login_captcha' => [ 'username', 'password', 'ci_captcha' ],

		'forget' => [ 'username', 'email' ],

		'forget_captcha' => [ 'username', 'email', 'ci_captcha' ]

	];

	public function __construct ( NknAuth\Config $config  = null )
	{
		$this->model = model( '\App\Models\Login' );
		$this->user = db_connect() ->table( 'user' );

		$this->config = $config ?: config( NknAuth\Config::class ) ;

		helper( 'cookie' );
	}

	/**
	 * @param string|array $needed
	 */
	public function rules ( $needed )
	{
		$generalRules = [

			'username' => [
				'label' => lang( 'NKnAuth.labelUsername' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_dash'
			],

			'password' => [
				'label' => lang( 'NKnAuth.labelPassword' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct'
			],

			'email' => [
				'label' => lang( 'NKnAuth.labelEmail' ),
				'rules' => 'trim|required|min_length[5]|max_length[128]|valid_email'
			],

			'ci_captcha' => [
				'label' => lang( 'NKnAuth.labelCaptcha' ),
				'rules' => 'trim|required|min_length[5]|ci_captcha'
			]
		];

		if ( is_string( $needed ) ) {
			$result = dot_array_search( $needed, $generalRules );
		}

		if ( is_array( $needed ) ) {
			$result = [];

			foreach ( $needed as $need ) {
				if ( isset( $generalRules[ $need ] ) )
				$result[ $need ] = $generalRules[ $need ];
			}
		}

		if ( empty( $result ) ) throw new \Exception( "Error rule not found", 1 );

		return $result;
	}

	/**
	 * @param boolean $returnType
	 *
	 * @param true $returnType get result as object
	 * @param false $returnType get result as array
	 *
	 * @return array
	 */
	public function login ( bool $returnType = true ) : array
	{
		static::$returnType = $returnType ? 'object' : 'array';

		# A little bit data here but ... just return self
		$this->typeChecker( 'login' );

		return $this->getMessage();
	}

	/** @read_more login */
	public function logout ( bool $returnType = true ) : array
	{
		# --- Todo: move to config
		static::$returnType = $returnType ? 'object' : 'array';
		$this->response[ 'success' ] = true;

		delete_cookie( $this->config->cookie );

		if ( Services::session() ->has( $this->config->session ) )
		{
			Services::session() ->destroy();
			$this->messageSuccess[] = lang( 'NknAuth.successLogout' );

			return $this->getMessage();
		}

		$errEl = 'You have not login. '.  lang( 'NknAuth.homeLink');
		$this->messageErrors[] = $errEl;

		return $this->getMessage();
	}

	/** @read_more login */
	public function requestPassword ( bool $returnType = true ) : array
	{
		# --- Todo: move to config
		$this->typeChecker( 'forget' );

		static::$returnType = true === $returnType ? 'object' : 'array';

		return $this->getMessage();
	}

	# --- Todo: not using
	public function in_group ( $menu = false )
	{
		$userdata = $this->getUserdata();
		$perm = $userdata[ 'permission' ][ 0 ] ?? null;

		if ( ! $userdata || null === $perm ) return false;

		if ( false !== $menu ) {

			if ( ! empty( $menu ) && is_array( $menu ) )
			{
				if ( $perm == 'all' ) return $menu;

				foreach ( $menu as $key => $val ) {
					if ( in_array( $val[ 'url' ], $userdata[ 'permission' ] ) ) {
						$response[ $key ] = $val;
					}
				}

				return array_values( $response );
			}

			else if ( is_string( $menu ) )
			{
				return ( $perm === 'all' )
				? true
				: in_array( $menu, $userdata[ 'permission' ] );
			}

			return false;
		}

		return $perm === 'all';
	}

	/** Set throttle config and get showCaptcha */
	private function throttleModel () : \App\Models\Login
	{
		$this->model->config(
			$this->config->throttle->type,
			$this->config->throttle->limit_one,
			$this->config->throttle->limit,
			$this->config->throttle->timeout
		);

		$this->response[ 'attemps' ] = $this->model->getAttempts();
		$this->response[ 'captcha' ] = $this->model->showCaptcha();

		return $this->model;
	}

	/**
	 * @param string $type login | forget
	 * @throws \Exception
	 * @return array|object|void
	 */
	private function typeChecker ( $type = 'login' )
	{
		if ( ! in_array( $type, [ 'login', 'forget' ] ) )
		{
			throw new \Exception( 'Type must be in [login or forget]', 1 );
		}

		$req = Services::request();
		$requestType = ( $type === 'login' ) ? 'password' : 'email';

		$isNullUsername = is_null( $req->getPostGet( 'username' ) );
		$isNullType = is_null( $req->getPostGet( $requestType ) );

		$hasRequest = ! $isNullUsername && ! $isNullType;

		if ( true === $this->isLogged( true ) )
		{
			( $type === 'forget' )
			? $this->response[ 'reset_incorrect' ] = true
			: $this->setLoggedInSuccess( $this->getUserdata() );

			return true;
		}

		if ( $wasLimited = $this->throttleModel() ->limited() )
		{
			$this->response[ 'limit_max' ] = $wasLimited;
			$timeout = $this->config->throttle->timeout;
			$errArg = [
				'num' => gmdate( 'i', $timeout ),
				'type' => 'minutes'
			];

			$this->messageErrors[] = lang(
				'NknAuth.errorThrottleLimitedTime',
				$errArg
			);

			return $this->messageErrors;
		}

		if ( false === $hasRequest ) {
			// Services::session()->destroy();
			return $this->response[ 'view' ] = true;
		}

		return ( $type === 'login' )
		? $this->loginHandler()
		: $this->forgotHandler();
	}

	/**
	 * @param string|null $key null return array user-session
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( false === $this->isLogged() ) return false;

		$ssName = $this->config->session;

		if ( empty( $key ) )
		return Services::session() ->get( $ssName );

		$userData = Services::session() ->get( $ssName );

		return $userData[ $key ]?? dot_array_search( $key, $userData );
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
		return ( static::$returnType === 'object' )
		? (object) $this->response
		: $this->response;
	}

	/**
	 * Receive all types of messages in this class
	 * @return array
	 */
	public function getMessage ( array $addMore = [] ) : array
	{
		$message = [
			'success' => $this->messageSuccess,
			'errors' => $this->messageErrors,
			'result' => $this->getResult()
		];

		empty( $addMore ) ?: $message += $addMore;

		return $message;
	}

	/**
	 * The first check the current user session,
	 * the next will be $data parameter
	 * @param array $data empty = 1st group = administrator
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
		if ( empty( $data ) ) return true;

		# --- Permission config
		$configPerm = config( '\BAPI\Config\User' )
		->setting( 'permission' );

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
	 * Check cookie, session: if have cookie will set session
	 * @return boolean
	 */
	public function isLogged ( bool $withCookie = false ) : bool
	{
		$ssName = $this->config->session;
		if ( true === Services::session() ->has( $ssName ) )
		return true;

		return ( false === $withCookie )
		? false
		: $this->cookieHandler();
	}

	public function cookieHandler () : bool
	{
		$userCookie = get_cookie( $this->config->cookie );
		if ( empty( $userCookie ) || ! is_string( $userCookie ) )
		return false;

		$exp = explode( '-', $userCookie, 2 );
		$incorrectCookie = function  () : bool
		{
			delete_cookie( $this->config->cookie );
			return false;
		};

		if ( empty( $exp[ 0 ] ) || empty( $exp[ 1 ] ) )
		return $incorrectCookie();

		$userId = hex2bin( $exp[ 1 ] );
		if ( $userId === '0' || ! ctype_digit( $userId ) )
		return $incorrectCookie();

		# Check token
		$user = $this->user
		->select( 'cookie_token, status, last_login, session_id' )
		->where( [ 'id' => $userId ] )
		->get(1)
		->getRowArray();
		if ( null === $user ) return $incorrectCookie();

		$userToken = password_verify(
			$user[ 'cookie_token' ],
			$exp[ 0 ]
		);

		$ip = Services::request() ->getIPAddress();
		$userIp = $user[ 'last_login' ] == $ip;

		if ( false === $userToken || false === $userIp )
		return $incorrectCookie();

		# Check status
		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) )
		{
			$this->denyStatus( $user[ 'status' ], false, false );
			return $incorrectCookie();
		}

		if ( false === $this->isMultiLogin( $user[ 'session_id' ] ) )
		{
			$this->denyMultiLogin( true, [], false );
			return false;
		}

		# --- Update cookie
		$logErr = "Cookie success checked, but error when update data: {$userId}";
		$this->setCookie( $userId, [], $logErr );

		# --- Create new session
		$userData = $this->user
		->select( implode( ',', $this->columnData() ) )
		->join( 'user_group', 'user_group.id = User.group_id' )
		->where( [ 'user.id' => $userId ] )
		->get(1)
		->getRowArray();

		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );

		Services::session() ->set(
			$this->config->session, $userData
		);

		$this->setTestCookie();

		return true;
	}

	private function loginInvalid ()
	{
		$validation = Services::Validation() ->withRequest( Services::request() );

		if ( true === $this->response[ 'captcha' ] )
		{
			$ruleCap = [ 'ci_captcha' => $this->rules( 'ci_captcha' ) ];

			if ( false === $validation ->setRules( $ruleCap ) ->run() )
			{
				$errStr = [ $validation->getError( 'ci_captcha' ) ];
				return $this->incorrectInfo( true, $errStr );
			}
		}

		$incorrectInfo = false;
		$ruleUsername = [ 'username' => $this->rules( 'username' ) ];

		if ( false === $validation ->setRules( $ruleUsername ) ->run() )
		{
			Services::Validation() ->reset();

			$ruleEmail = [ 'username' => $this->rules( 'email' ) ];
			$validation = Services::Validation() ->withRequest( Services::request() );
			$incorrectInfo = ! $validation->setRules( $ruleEmail ) ->run();
		}

		false === $incorrectInfo ?: $this->incorrectInfo( true );

		return $incorrectInfo;
	}

	private function loginAfterValidation () : array
	{
		$rawEmail = Services::request() ->getPostGet( 'username' );

		$userData = $this->user
		->select( implode( ',', $this->columnData( [ 'password' ] ) ) )
		->join( 'user_group', 'user_group.id = user.group_id' )
		->where( [ 'user.username' => $rawEmail ] )
		->orWhere( [ 'user.email' => $rawEmail ] )
		->get( 1 )
		->getRowArray();

		if ( null === $userData ) {
			return [ 'error' => $this->incorrectInfo() ];
		}

		$rawPassword = Services::request() ->getPostGet( 'password' );
		$verifyPassword = $this->getVerifyPass( $rawPassword, $userData[ 'password' ] );

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

	/**
	 * Validation form login and set session & cookie
	 * @return array|object|void
	 */
	private function loginHandler ()
	{
		if ( false !== ( $invalid = $this->loginInvalid() ) )
		return $invalid;

		$userData = $this->loginAfterValidation();
		if ( array_key_exists( 'error', $userData ) )
		return $userData[ 'error' ] ?? $userData;

		# --- Set true response success
		$this->setLoggedInSuccess( $userData );
		# --- Set user session
		Services::session() ->set( $this->config->session, $userData );

		$userId = $userData[ 'id' ];
		if ( Services::request() ->getPostGet( 'remember_me' ) )
		{
			$this->setCookie( $userId );
		}
		else if ( false === $this->loggedInUpdateData( $userId ) )
		{
			log_message( 'error', "{$userId} Logged-in, but update failed" );
		}

		$this->setTestCookie();
		$this->model->throttle_cleanup();
	}

	/**
	 * Validation form forgot password and send mail
	 * @return array|object|void
	 */
	private function forgotHandler ()
	{
		$group = $this->model->showCaptcha()
		? 'forget_captcha'
		: 'forget';

		Services::Validation()
		->withRequest( Services::request() )
		->setRules( $this->rules( $this->rules[ $group ] ) );

		if ( false === Services::Validation() ->run() ) {
			$errors = Services::Validation() ->getErrors();
			return $this->incorrectInfo( true, array_values( $errors ) );
		}

		$whereQuery = [
			'username' => Services::request() ->getPostGet( 'username' ),
			'email' => Services::request() ->getPostGet( 'email' )
		];

		$find_user = $this->user
		->select( 'username' )
		->where( $whereQuery )
		->get()
		->getRowArray();

		if ( null === $find_user ) {
			return $this->incorrectInfo();
		}

		helper( 'text' );
		$randomPw = random_string();
		$hashPw = Services::NknAuth()->getHashPass( $randomPw );

		$updatePassword = $this->user ->update(
			[ 'password' => $hashPw ],
			[ 'username' => $find_user[ 'username' ] ]
		);

		$error = 'The system is busy, please come back later';

		if ( ! $updatePassword ) {
			$this->messageErrors[] = $error;

			return false;
		}

		if ( ! $this->mailSender( $randomPw ) ) {

			$this->messageErrors[] = $error;
			log_message(
				'error' ,
				"Cannot sent email: {$find_user[ 'username' ]}"
			);

			return false;
		}

		$this->response[ 'success' ] = true;
		$this->messageSuccess[] = lang( 'NknAuth.successResetPassword' );
	}

	private function isMultiLogin ( string $session_id ) : bool
	{
		$config = config( '\Config\App' );
		$pathFile = $config->sessionSavePath;
		$pathFile .= '/' . $config->sessionCookieName . $session_id;

		helper( 'filesystem' );

		if ( empty( $date = get_file_info( $pathFile, 'date' ) ) ) {
			return true;
		}

		$cookieName = $config->sessionCookieName . '_test';
		if ( $hash = get_cookie( $cookieName ) ) {

			if ( password_verify( $session_id, $hash ) ) {
				return true;
			}

			delete_cookie( $cookieName );
			return false;
		}

		$time = ( time() - $date[ 'date' ] );
		$sessionExp = (int) $config->sessionExpiration;

		if ( $sessionExp > 0 ) {
			return $time < $sessionExp ? false : true;
		}

		if ( $sessionExp === 0 ) {
			return $time < $config->sessionTimeToUpdate ? false : true;
		}

		$this->messageErrors[] = "else";
		return false;
	}

	/** @read_more getMessage */
	private function denyMultiLogin (
		bool $throttle = true, array $addMore = [], $getReturn = true
	)
	{
		false === $throttle ?: $this->response[ 'attemps' ] = $this->model->throttle();
		$this->response[ 'view' ] = true;
		$this->response[ 'login_incorrect' ] = true;

		$errors[] = lang( 'NknAuth.noteLoggedInAnotherPlatform' );
		$this->messageErrors = [ ...$errors, ...$addMore ];

		if ( true === $getReturn ) return $this->getMessage();
	}

	/** @read_more getMessage */
	private function incorrectInfo (
		bool $throttle = true,
		array $addMore = []
	)
	{
		false === $throttle ?: $this->response[ 'attemps' ] = $this->model->throttle();
		$this->response[ 'view' ] = true;
		$this->response[ 'login_incorrect' ] = true;

		$errors[] = lang( 'NknAuth.errorIncorrectInformation' );
		$this->messageErrors = [ ...$errors, ...$addMore ];

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
		false === $throttle ?: $this->response[ 'attemps' ] = $this->model->throttle();
		$this->response[ 'banned' ] = $status === 'banned';
		$this->response[ 'inactive' ] = $status === 'inactive';
		$this->messageErrors[] = lang( 'NknAuth.errorNotReadyYet', [ $status ] );

		if ( true === $getReturn ) return $this->getMessage();
	}

	private function setCookie (
		int $userId,
		array $data = [],
		string $logError = null
	) : void
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception( lang( 'Validation.greater_than', $errArg ), 1 );
		}

		if ( ! empty( $data ) && false === isAssoc( $data ) ) {
			throw new \Exception( lang( 'NknAuth.isAssoc' ), 1 );
		}

		$randomKey = Encryption::createKey( 8 );
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
			log_message( 'error', $logErr );
		}
	}

	private function setLoggedInSuccess ( array $userData ) : void
	{
		# --- Set success to true
		$this->response[ 'success' ] = true;
		$this->messageSuccess[] = lang( 'NknAuth.successLoggedWithUsername', [ $userData[ 'username' ] ] );
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	private function loggedInUpdateData ( int $userId, array $data = [] )
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];

			throw new \Exception( lang( 'Validation.greater_than', $errArg ), 1 );
		}

		if ( ! empty( $data ) && false === isAssoc( $data ) ) {
			throw new \Exception( lang( 'NknAuth.isAssoc' ), 1 );
		}

		$ssId = session_id();
		$updateData = [
			'last_login' => Services::request() ->getIPAddress(),
			'last_activity' => date( 'Y-m-d H:i:s' ),
			'session_id' => $ssId
		];

		$updateData += $data;

		return $this->user->update( $updateData, [ 'id' => $userId ], 1 );
	}

	public function setTestCookie () : void
	{
		$config = config( '\Config\App' );
		$cookieValue = password_hash( session_id(), PASSWORD_DEFAULT );
		$ttl = $config->sessionTimeToUpdate;
		// $cookieName = $config->sessionCookieName;
		$cookieName = $this->config->cookie;

		// setcookie( $cookieName . '_test', $cookieValue, $ttl, '/' );
		set_cookie( $cookieName . '_test', $cookieValue, $ttl );
	}

	private function columnData ( array $addMore = [] ) : array
	{
		$colum = [
			# user
			'user.id',
			'user.username',
			// 'user.password',
			'user.email',
			'user.status',
			'user.last_activity',
			'user.last_login',
			'user.created_at',
			'user.updated_at',
			'user.session_id',
			# user_group
			'user_group.id as group_id',
			'user_group.name as group_name',
			'user_group.permission',
			...$addMore
		];

		return $colum;
	}

	private function trigger ( string $event, array $eventData )
	{
		if ( ! isset( $this->{$event} ) || empty( $this->{$event} ) ) return $eventData;

		foreach ( $this->{$event} as $callback )
		{
			if ( ! method_exists( $this, $callback ) ) {
				throw DataException::forInvalidMethodTriggered($callback);
			}

			$eventData = $this->{$callback}( $eventData );
		}

		return $eventData;
	}

	private function mailSender ( string $randomPw ) : bool
	{
		/**
		 * @var \CodeIgniter\Email\Email
		 */
		$email = Services::email();

		$email
		->setFrom ( 'localhost@example.com', 'Administrator' )
		->setTo ( 'cukikt0302@gmail.com' )
		->setCC ( 'another@another-example.com' )
		->setBCC ( 'them@their-example.com' )
		->setSubject ( 'Email Test' )
		->setMessage ( 'your password has been reset to: ' . $randomPw );

		if ( ! $email->send() ) {
			ENVIRONMENT ==! 'production'
			? d( $email->printDebugger() )
			: log_message( 'error', $email->printDebugger() );

			return false;
		}

		return true;
	}
}