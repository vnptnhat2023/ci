<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Encryption\Encryption;
use Config\Services;

class NknAuth
{
	private \Config\Nkn $NknConfig;

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
		'login' => [
			'username',
			'password'
		],
		'login_captcha' => [
			'username',
			'password',
			'reCaptcha3'
		],
		'forget' => [
			'username',
			'email'
		],
		'forget_captcha' => [
			'username',
			'email',
			'reCaptcha3'
			]
	];

	public function __construct ( /*BaseConfig $config*/ )
	{
		$this->model = new \App\Models\Login();

		$this->user = db_connect() ->table( 'user' );

		helper( 'cookie' );

		# Will move to auth config folder
		$this->NknConfig = new \Config\Nkn();
		// $this->setConfig( $config )
	}

	/**
	 * @param string|array $needed
	 */
	public function rules( $needed )
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
				'rules' => 'trim|required|max_length[10]|ci_captcha'
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
	 * @true object
	 * @false array
	 */
	public function login ( bool $returnType = true ) : self
	{
		static::$returnType = $returnType ? 'object' : 'array';

		# A little bit data here but ... just return self
		$this->typeChecker( 'login' );

		return $this;
	}

	/** @read_more login */
	public function logout ( bool $returnType = true ) : self
	{
		# --- Todo: move to config
		static::$returnType = $returnType ? 'object' : 'array';

		delete_cookie( $this->getConfig()->cookieName );

		if ( Services::session() ->has( $this->getConfig()->sessionName ) ) {
			Services::session() ->destroy();

			$this->messageSuccess[] = lang( 'NknAuth.successLogout' );
		}

		$this->response['success'] = true;

		return $this;
	}

	/** @read_more login */
	public function forgetPass ( bool $returnType = true ) : self
	{
		# --- Todo: move to config
		$this->typeChecker( 'forget' );

		static::$returnType = true === $returnType ? 'object' : 'array';

		return $this;
	}

	# --- Todo: not using
	public function in_group ( $menu = false )
	{
		$userdata = $this->getUserdata();

		if ( ! $userdata || ! isset( $userdata[ 'permission' ][0] )  ) return false;

		if ( $menu )
		{
			if ( is_array( $menu ) && isset( $menu[0] ) )
			{

				if ( $userdata[ 'permission' ][0] == 'all' ) return $menu;

				foreach ( $menu as $key => $val )
				{
					if ( in_array( $val[ 'url' ], $userdata[ 'permission' ] ) )

					$response[ $key ] = $val;
				}

				return array_values( $response );
			}
			else if ( is_string( $menu ) )
			{
				if ( $userdata[ 'permission' ][0] == 'all' ) return true;

				return in_array( $menu, $userdata[ 'permission' ] );
			}
			return false;

		}
		else
		{
			if ( $userdata[ 'permission' ][0] == 'all' ) return true;

			return false;
		}
	}

	/**
	 * @var \Config\Nkn::getConfig $NknConfig
	 */
	public function getConfig () : object
	{
		return $this->NknConfig->getConfig();
	}

	# --- Todo: maybe not use it
	private function setConfig (array $data) : object
	{
		return $this->NknConfig->setConfig( $data );
	}

	/** Set throttle config and get was_limited_one */
	private function model () : \App\Models\Login
	{
		$throttle = $this->getConfig()->throttle;

		$model = $this->model->config(
			$throttle->type,
			$throttle->limit_one,
			$throttle->limit,
			$throttle->timeout
		);

		$this->response[ 'attemps' ] = $this->model->getAttempts();
		$this->response[ 'captcha' ] = $this->model->was_limited_one();

		return $model;
	}

	/**
	 * @param string $type login | forget
	 * @throws \Exception
	 * @return array|object|void
	 */
	private function typeChecker ( $type = 'login' )
	{
		if ( ! in_array( $type, [ 'login', 'forget' ] ) ) {
			throw new \Exception( 'Type must be in [login or forget]', 1 );
		}

		$requestType = ( $type === 'login' ) ? 'password' : 'email';
		$isNullUsername = is_null( Services::request() ->getPostGet( 'username' ) );
		$isNullType = is_null( Services::request() ->getPostGet( $requestType ) );

		$hasRequest = ! $isNullUsername && ! $isNullType;

		if ( true === $this->isLogged( true ) ) {

			if ( $type === 'forget' )
			{
				$this->response[ 'reset_incorrect' ] = true;
			}
			else
			{
				$this->setLoggedInSuccess( $this->getUserdata() );
			}

			return true;
		}

		# --- Todo: Loss was_limited_one: show captcha here !
		if ( $wasLimited = $this->model() ->was_limited() ) {
			$this->response[ 'limit_max' ] = $wasLimited;

			$errArg = [ $this->getConfig()->throttle->timeout ];
			$this->messageErrors[] = lang( 'NknAuth.errorThrottleLimitedTime', $errArg );

			return $this->messageErrors;
		}

		if ( false === $hasRequest ) return $this->response[ 'view' ] = true;

		return ( $type === 'login' ) ? $this->loginHandler() : $this->forgotHandler();
	}

	/**
	 * @param string|null $key null return array user-session
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( false === $this->isLogged() ) return false;

		if ( empty( $key ) ) {
			return Services::session() ->get( $this->getConfig()->sessionName );
		}

		$userData = Services::session() ->get( $this->getConfig()->sessionName );

		return $userData[ $key ] ?? dot_array_search( $key, $userData );
	}

	public function getHashPass ( string $password, int $cost = 12 ) : string
  {
		$salt = password_hash( $password, PASSWORD_BCRYPT, [ 'cost' => $cost ] );

    return $salt;
  }

  public function getVerifyPass ( string $password, string $salt ) : bool
  {
  	return password_verify( $password, $salt );
	}

	/**
	 * @return object|array
	 */
	public function getResult ()
	{
		return static::$returnType === 'object' ? (object) $this->response : $this->response;
	}

	/**
	 * Receive all types of messages in this class
	 * @return object|array Depend on property $returnType
	 */
	public function getMessage ( array $addMore = [] )
	{
		$message = [
			'success' => $this->messageSuccess,
			'errors' => $this->messageErrors,
			'result' => $this->getResult()
		];

		empty( $addMore ) ?: $message += $addMore;

		return static::$returnType === 'object' ? (object) $message : $message;
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

		if ( ( false === $userPerm ) || empty( $userPerm ) ) return false;

		if ( in_array( 'null', $userPerm, true ) ) return false;

		if ( in_array( 'all', $userPerm, true ) ) return true;

		# --- Administrator (1st) group !
		if ( empty( $data ) ) return true;

		# --- Permission config
		$configPerm = config( '\BAPI\Config\User' ) ->setting( 'permission' );
		$boolVar = true;

		foreach ( $data as $role ) {
			$inCfPerm = in_array( $role, $configPerm, true );
			$inUserPerm = in_array( $role, $userPerm, true );

			if ( false === $inCfPerm || false === $inUserPerm ) {
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
		if ( true === Services::session() ->has( $this->getConfig()->sessionName ) ) return true;

		return false === $withCookie ? false : $this->cookieHandler();
	}

	public function cookieHandler () : bool
	{
		$userCookie = get_cookie( $this->getConfig()->cookieName );
		if ( empty( $userCookie ) || ! is_string( $userCookie ) ) return false;

		$exp = explode( '-', $userCookie, 2 );

		$incorrectCookie = function  () : bool {
			delete_cookie( $this->getConfig()->cookieName );
			return false;
		};

		if ( empty( $exp[ 0 ] ) || empty( $exp[ 1 ] ) ) return $incorrectCookie();

		$userId = hex2bin( $exp[ 1 ] );
		if ( $userId === '0' || ! ctype_digit( $userId ) ) return $incorrectCookie();

		# Check token
		$user = $this->user
		->select( 'cookie_token, status, last_login' )
		->where( [ 'id' => $userId ] )
		->get(1)
		->getRowArray();
		if ( null === $user ) return $incorrectCookie();

		$userToken = password_verify( $user[ 'cookie_token' ], $exp[ 0 ] );
		$userIp = $user[ 'last_login' ] == Services::request() ->getIPAddress();

		if ( false === $userToken || false === $userIp ) return $incorrectCookie();

		# Check status
		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) ) {
			$this->denyStatus( $user[ 'status' ], false, false );
			return $incorrectCookie();
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

		Services::session() ->set( $this->getConfig()->sessionName, $userData );

		return true;
	}

	/**
	 * Validation form login and set session & cookie
	 * @return array|object|void
	 */
	private function loginHandler ()
	{
		$validation = Services::Validation() ->withRequest( Services::request() );

		if ( true === $this->response[ 'captcha' ] ) {
			$ruleCaptcha = [ 'ci_captcha' => $this->rules( 'ci_captcha' ) ];

			if ( false === $validation->setRules( $ruleCaptcha )->run() ) {
				return $this->incorrectInfo( true, [ $validation->getError('ci_captcha') ] );
			}
		}

		$incorrectInfo = false;

		$ruleUsername = [ 'username' => $this->rules( 'username' ) ];

		# --- Todo: add more captcha rule
		if ( false === $validation->setRules( $ruleUsername )->run() ) {
			Services::Validation() ->reset();

			$ruleEmail = [ 'username' => $this->rules( 'email' ) ];
			$validation = Services::Validation() ->withRequest( Services::request() );
			$incorrectInfo = ! $validation->setRules( $ruleEmail ) ->run();
		}

		if ( true === $incorrectInfo ) return $this->incorrectInfo( true );

		$userEmail = Services::request() ->getPostGet( 'username' );
		$userData = $this->user
		->select( implode( ',', $this->columnData() ) )
		->join( 'user_group', 'user_group.id = user.group_id' )
		->where( [ 'user.username' => $userEmail ] )
		->orWhere( [ 'user.email' => $userEmail ] )
		->get(1)
		->getRowArray();

		if ( null === $userData ) return $this->incorrectInfo();

		$verifyPassword = $this->getVerifyPass(
			Services::request() ->getPostGet( 'password' ),
			$userData[ 'password' ]
		);

		if ( false === $verifyPassword ) return $this->incorrectInfo();

		if ( 'active' !== $userData[ 'status' ] )
		return $this->denyStatus( $userData['status'] );

		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );

		# --- Set true response success
		$this->setLoggedInSuccess( $userData );

		# --- Set user session
		Services::session() ->set( $this->getConfig()->sessionName, $userData );

		if ( Services::request() ->getPostGet( 'remember_me' ) )
		{
			$this->setCookie( $userData[ 'id' ] );
		}
		else if ( false === $this->loggedInUpdateData( $userData[ 'id' ] ) )
		{
			log_message( 'error', "{$userData[ 'id' ]} Logged-in, but update failed" );
		}

		# --- Todo: add an event for clean throttle, update after login
		# --- Cleanup throttle
		$this->model->throttle_cleanup();
	}

	/**
	 * Validation form forgot password and send mail
	 * @return array|object|void
	 */
	private function forgotHandler ()
	{
		$group = $this->model->was_limited_one() ? 'forget_captcha' : 'forget';

		Services::Validation()
		->withRequest( Services::request() )
		->setRules( $this->rules( $this->rules[ $group ] ) );

		if ( false === Services::Validation() ->run() ) return $this->incorrectInfo();

		$whereQuery = [
			'username' => Services::request() ->getPostGet( 'username' ),
			'email' => Services::request() ->getPostGet( 'email' )
		];

		$find_user = $this->user
		->select( 'username' )
		->where( $whereQuery )
		->get()
		->getRowArray();

		if ( null === $find_user ) return $this->incorrectInfo();

		$this->response[ 'success' ] = true;
		$this->messageSuccess[] = lang( 'NknAuth.successResetPassword' );
	}

	/** @read_more getMessage */
	private function incorrectInfo ( bool $throttle = true, array $addMore = [] )
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
	private function denyStatus (string $status, bool $throttle = true, $getReturn = true )
	{
		false === $throttle ?: $this->response[ 'attemps' ] = $this->model->throttle();
		$this->response[ 'banned' ] = $status === 'banned';
		$this->response[ 'inactive' ] = $status === 'inactive';
		$this->messageErrors[] = lang( 'NknAuth.errorNotReadyYet', [ $status ] );

		if ( true === $getReturn ) return $this->getMessage();
	}

	private function setCookie ( int $userId, array $data = [], string $logError = null ) : void
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
			$ttl = time() + $this->getConfig()->cookieTTL;
			setcookie( $this->getConfig()->cookieName, $cookieValue, $ttl, '/' );
		}
		else
		{
			$logErr = $logError ?: "{$userId} LoggedIn with remember-me, but update failed";
			log_message( 'error', $logErr );
		}
	}

	private function setLoggedInSuccess( array $userData ) : void
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

		$updateData = [
			'last_login' => Services::request() ->getIPAddress(),
			'last_activity' => date( 'Y-m-d H:i:s' ),
			'session_id' => session_id()
		];

		$updateData += $data;

		return $this->user->update( $updateData, [ 'id' => $userId ], 1 );
	}

	private function columnData ( array $add = [] ) : array
	{
		$colum = [
			# user
			'user.id',
			'user.username',
			'user.password',
			'user.email',
			'user.status',
			'user.last_activity',
			'user.last_login',
			'user.created_at',
			'user.updated_at',
			# user_group
			'user_group.id as group_id',
			'user_group.name as group_name',
			'user_group.permission',
			...$add
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

	private function _sentMail ()
	{
		$email = Services::email();
    $config[ 'protocol' ] = 'smtp';
    // $config[ 'mailPath' ] = '/usr/sBin/sendMail';
    $config[ 'SMTPHost' ] = "smtp.gmail.com";
    $config[ 'SMTPUser' ] = "user@gmail.com";
    $config[ 'SMTPPass' ] = "password";
    $config[ 'SMTPPort' ] = 587;
    $config[ 'SMTPCrypto' ] = "tls";
    $config[ 'mailType' ] = "text";
    $config[ 'validation' ] = false;
    $config[ 'newline' ] = "\r\n";
    $email->initialize( $config);
    $email->setFrom("noreply@host.com", "Test User");
    $email->setTo( 'asdadsd@receiverEmail.org' );
    $email->setSubject( 'This is a test' );
    $email->setMessage( 'Testing the email class.' );
    if ( $email->send()) {
        return true;
    } else {
        return $email->printDebugger();
    }
	}
}