<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Encryption\Encryption;
use Config\Services;

/**
 * Created: nguyenkenhat@outlook.com
 * Rewrite 30/06/2020 21:49:00
 * Date 19/03/2017 12:04:28
 * Contain plugins
 * Throttle https://github.com/joeylevy/CI_throttle/blob/master/library/Throttle
 * cookie fix at https://github.com/codeigniter4/CodeIgniter4/pull/2709
 */
class NknAuth
{
	private \Config\Nkn $NknConfig;

	# --- Todo: write more
	// private array $config = [];

	protected array $response = [
		# --- When logged-in but request forget password
		'reset_incorrect' => false,
		'login_incorrect' => false,
		'success' => false,
		'view' => false,
		'captcha' => false,
		'limit_max' => false,
		# --- Times to show captcha, should change to: limited_level
		'was_limited_one' => false,
		'attemps' => null,
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

	public function __construct ()
	{
		$this->model = new \App\Models\Login();

		$this->user = db_connect() ->table( 'user' );

		helper( [ 'array', 'cookie' ] );

		$this->NknConfig = new \Config\Nkn();
	}

	public function rules( string $key )
	{
		$rules = [
			'login' => [
				'username' => [
					// 'label' => lang( 'NKnAuth.labelUsername' ),
					'label' => lang( 'NKnAuth.labelUserOrEmail' ),
					// 'rules' => 'trim|min_length[5]|max_length[32]|alpha_dash',
					'rules' => 'trim|min_length[5]|max_length[128]|valid_email'
				],
				'password' => [
					'label' => lang( 'NKnAuth.labelPassword' ),
					'rules' => 'trim|min_length[5]|max_length[32]|alpha_numeric_punct'
				]
			],
			'login_with_captcha' => [
				'username' => [
					// 'label' => lang( 'NKnAuth.labelUsername' ),
					'label' => lang( 'NKnAuth.labelUserOrEmail' ),
					// 'rules' => 'trim|min_length[5]|max_length[32]|alpha_dash',
					'rules' => 'trim|min_length[5]|max_length[128]|valid_email'
				],
				'password' => [
					'label' => lang( 'NKnAuth.labelPassword' ),
					'rules' => 'trim|min_length[5]|max_length[32]|alpha_numeric_punct'
				],
				'ci_captcha' => [
					'label' => lang( 'NKnAuth.labelCaptcha' ),
					'rules' => 'required|trim|max_length[10]|captcha_ci'
				]
			],
			'forgot_password' => [
				'username' => [
					'label' => lang( 'NKnAuth.labelUsername' ),
					'rules' => 'trim|min_length[5]|max_length[32]alpha_dash'
				],
				'email' => [
					'label' => lang( 'NKnAuth.labelEmail' ),
					'rules' => 'trim|min_length[5]|max_length[128]|valid_email'
				]
			],
			'forgot_password_with_captcha' => [
				'username' => [
					'label' => lang( 'NKnAuth.labelUsername' ),
					'rules' => 'trim|min_length[5]|max_length[32]alpha_dash'
				],
				'email' => [
					'label' => lang( 'NKnAuth.labelEmail' ),
					'rules' => 'trim|min_length[5]|max_length[128]|valid_email'
				],
				'ci_captcha' => [
					'label' => lang( 'NKnAuth.labelCaptcha' ),
					'rules' => 'required|trim|max_length[10]|captcha_ci'
				]
			]
		];

		if ( empty( $rules[ $key ] ) ) {
			throw new \Exception( "Error rule: [ {$key} ] not found", 1 );
		}

		return $rules[ $key ];
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
		static::$returnType = $returnType ? 'object' : 'array';

		delete_cookie( $this->NknConfig::NKNck );

		if ( Services::session() ->has( $this->NknConfig::NKNss ) ) {
			Services::session() ->remove( $this->NknConfig::NKNss );

			$this->messageSuccess[] = lang( 'NknAuth.successLogout' );
		}

		return $this;
	}

	/** @read_more login */
	public function forgetPass ( bool $returnType = true ) : self
	{
		$this->typeChecker( 'forgot_password' );

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

	public function getConfig () : object
	{
		$data = [
			'session_name' => $this->NknConfig::NKNss,
			'cookie_name' => $this->NknConfig::NKNck,
			'throttle' => $this->NknConfig::throttle,
		];

		return (object) $data;
	}

	/** Set throttle config and get was_limited_one */
	private function model () : \App\Models\Login
	{
		$model = $this->model->config(
			$this->NknConfig::throttle[ 'type' ],
			$this->NknConfig::throttle[ 'limit_one' ],
			$this->NknConfig::throttle[ 'limit' ],
			$this->NknConfig::throttle[ 'timeout' ]
		);

		$this->response[ 'captcha' ] = $this->model->was_limited_one();

		return $model;
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( false === $this->isLogged() ) return false;

		if ( empty( $key ) ) {
			return Services::session() ->get( $this->NknConfig::NKNss );
		}

		$userData = Services::session() ->get( $this->NknConfig::NKNss );

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
	 * Check in current User Session first, will be to $data
	 * @param array $data when empty = 1st group = administrator
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
		if ( true === Services::session() ->has( $this->NknConfig::NKNss ) ) return true;

		return false === $withCookie ? false : $this->cookieHandler();
	}

	public function cookieHandler () : bool
	{
		$userCookie = get_cookie( $this->NknConfig::NKNck );
		if ( empty( $userCookie ) || ! is_string( $userCookie ) ) return false;

		$exp = explode( '-', $userCookie, 2 );

		$incorrectCookie = function  () : bool {
			delete_cookie( $this->NknConfig::NKNck );
			return false;
		};

		if ( empty( $exp[ 0 ] ) || empty( $exp[ 1 ] ) ) {
			return $incorrectCookie();
		}

		$userId = hex2bin( $exp[ 1 ] );
		if ( $userId === '0' || ! ctype_digit( $userId ) ) {
			return $incorrectCookie();
		}

		$user = $this->user
		->select( 'cookie_token, status, last_login' )
		->where( [ 'id' => $userId ] )
		->get(1)
		->getRowArray();

		if ( null === $user ) { return $incorrectCookie(); }

		$userToken = password_verify( $user[ 'cookie_token' ], $exp[ 0 ] );
		$userIp = $user[ 'last_login' ] == Services::request() ->getIPAddress();
		if ( false === $userToken || false === $userIp ) {
			return $incorrectCookie();
		}

		# Checking user status
		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) ) {
			$this->denyStatus( $user[ 'status' ], false, false );

			return $incorrectCookie();
		}

		# --- Already exist checked, no need do it again
		$userData = $this->user
		->select( implode( ',', $this->loginColumns() ) )
		->join( 'user_group', 'user_group.id = User.group_id' )
		->where( [ 'user.id' => $userId ] )
		->get(1)
		->getRowArray();

		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );

		Services::session() ->set( $this->NknConfig::NKNss, $userData );

		return true;
	}

	/**
	 * @param string $type login | forgot_password
	 * @throws \Exception
	 * @return array|object|void
	 */
	private function typeChecker ( $type = 'login' )
	{
		if ( ! in_array( $type, [ 'login', 'forgot_password' ] ) ) {
			throw new \Exception( 'Type must be in [login or forgot_password]', 1 );
		}

		$requestType = ( $type === 'login' ) ? 'password' : 'email';
		$isNullUsername = is_null( Services::request() ->getPostGet( 'username' ) );
		$isNullType = is_null( Services::request() ->getPostGet( $requestType ) );

		$hasRequest = ! $isNullUsername && ! $isNullType;

		if ( true === $this->isLogged( true ) ) {

			if ( $type === 'forgot_password' )
			{
				$this->response[ 'reset_incorrect' ] = true;
			}
			else
			{
				$this->setLoggedInSuccess();
			}

			return true;
		}

		# --- Todo: Loss was_limited_one: show captcha here !
		if ( $wasLimited = $this->model() ->was_limited() ) {
			$this->response[ 'limit_max' ] = $wasLimited;

			$errArg = [ $this->NknConfig::throttle[ 'timeout' ] ];
			$this->messageErrors[] = lang( 'NknAuth.errorThrottleLimitedTime', $errArg );

			return $this->messageErrors;
		}

		if ( false === $hasRequest ) return $this->response[ 'view' ] = true;

		return ( $type === 'login' ) ? $this->loginValidate() : $this->forgetValidate();
	}

	/**
	 * Validation form login and set session & cookie
	 * @return array|object|void
	 */
	private function loginValidate ()
	{
		$type = $this->model->was_limited_one()
		? 'login_with_captcha' : 'login';

		Services::Validation()
		->withRequest( Services::request() )
		->setRules( $this->rules( $type ) );

		if ( false === Services::Validation() ->run() )
		return $this->incorrectInfo( true, Services::Validation() ->getErrors() );

		$userData = $this->user
		->select( implode( ',', $this->loginColumns() ) )
		->join( 'user_group', 'user_group.id = user.group_id' )
		->where( [ 'username' => Services::request() ->getPostGet( 'username' ) ] )
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

		# --- Set success to true
		$this->setLoggedInSuccess();

		# --- Set user session
		Services::session() ->set( $this->NknConfig::NKNss, $userData );

		if ( Services::request() ->getPostGet( 'remember_me' ) )
		{
			$this->setCookie( $userData );
		}
		else if ( false === $this->loggedInUpdateLog( $userData[ 'id' ] ) )
		{
			log_message( 'error', "{$userData[ 'id' ]} Logged-in, but update failed" );
		}

		# --- Cleanup throttle
		$this->model->throttle_cleanup();
	}

	/**
	 * Validation form forgot password and send mail
	 * @return array|object|void
	 */
	private function forgetValidate ()
	{
		$validate_group = $this->model->was_limited_one()
		? 'forgot_password_with_captcha'
		: 'forgot_password';

		Services::Validation()
		->withRequest( Services::request() )
		->setRules( $this->rules( $validate_group ) );

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
		$this->messageErrors[] = lang( 'NknAuth.errorIncorrectInformation' );
		$this->messageErrors += $addMore;

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

	private function setCookie ( array $userData ) : void
	{
		$randomKey = Encryption::createKey( 8 );
		$idHex = bin2hex( $userData[ 'id' ] );
		$keyHex = bin2hex( $randomKey );
		$keyHash = password_hash( $keyHex, PASSWORD_DEFAULT );
		$cookieValue = "{$keyHash}-{$idHex}";

		$updateSuccess = $this->loggedInUpdateLog(
			$userData[ 'id' ], [ 'cookie_token' => $keyHex ]
		);

		# Lol don't know why need set cookie after using dbQuery class
		if ( true === $updateSuccess )
		{
			$ttl = time() + $this->NknConfig::NKNckTtl;
			// set_cookie( $this->NknConfig::NKNck, $cookieValue, (string) $ttl, '/' );
			setcookie( $this->NknConfig::NKNck, $cookieValue, $ttl, '/' );
		}
		else
		{
			$logErr = "{$userData[ 'id' ]} Logged::remember-me, but update failed";
			log_message( 'error', $logErr );
		}
	}

	private function setLoggedInSuccess() : void
	{
		# --- Set success to true
		$this->response[ 'success' ] = true;
		$this->messageSuccess[] = lang( 'NknAuth.successLogged' );
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	private function loggedInUpdateLog ( int $userId, array $data = [] )
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];

			throw new \Exception( lang( 'Validation.greater_than', $errArg ), 500 );
		}

		helper( 'array' );

		if ( ! empty( $data ) && false === isAssoc( $data ) ) {
			throw new \Exception( 'Data must be an associative array', 500 );
		}

		$updateData = [
			'last_login' => Services::request() ->getIPAddress(),
			'last_activity' => date( 'Y-m-d H:i:s' )
		];

		$updateData = $updateData + $data;

		return $this->user->update( $updateData, [ 'id' => $userId ], 1 );
	}

	private function loginColumns ( array $add = [] ) : array
	{
		$colum = [
			# user
			'user.id',
			'user.username',
			'user.password',
			'user.email',
			'user.status',
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