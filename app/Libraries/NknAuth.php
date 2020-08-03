<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Model;
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

	private array $response = [
		'forgot_password_denny' => false, // if loGed set "denny" to true (forgot password)
		'wrong' => false, // Wrong username or password
		'success' => false, // if success or has cookie
		'load_view' => false, // load form
		'show_captcha' => false,
		'was_limited' => false,// if true, return $throttle_config[timeout]
		'was_limited_one' => false,
		'attemps' => null,
		'banned' => false,
		'inactive' => false
	];

	/**
	 * @var \App\Models\Login $modelLogin
	 */
	protected \App\Models\Login $modelLogin;

	/**
	 * @var \CodeIgniter\Database\BaseBuilder $builder
	 */
	public BaseBuilder $builder;

	private array $rules = [
	  'login' => [
	  	'username' => [
	      'label' => 'Username',
	      'rules' => 'trim|min_length[5]|max_length[32]|alpha_dash'
	  	],
	  	'password' => [
	      'label' => 'Password',
	      'rules' => 'trim|min_length[5]|max_length[32]|alpha_numeric_punct'
	    ]
	  ],
	  'login_with_captcha' => [
	  	'username' => [
	      'label' => 'Username',
	      'rules' => 'trim|min_length[5]|max_length[32]|alpha_dash'
	    ],
	    'password' => [
	      'label' => 'Password',
	      'rules' => 'trim|min_length[5]|max_length[32]|alpha_numeric_punct'
	    ],
	    'ci_captcha' => [
	      'label' => 'Captcha',
	      'rules' => 'required|trim|max_length[10]|captcha_ci'
	    ]
	  ],
	  'forgot_password' => [
	  	'username' => [
	      'label' => 'Username',
	      'rules' => 'trim|min_length[5]|max_length[32]alpha_dash'
	    ],
	    'email' => [
	      'label' => 'Email',
	      'rules' => 'trim|min_length[5]|max_length[128]|valid_email'
	    ]
	  ],
	  'forgot_password_with_captcha' => [
	  	'username' => [
	      'label' => 'Username',
	      'rules' => 'trim|min_length[5]|max_length[32]alpha_dash'
	    ],
	    'email' => [
	      'label' => 'Email',
	      'rules' => 'trim|min_length[5]|max_length[128]|valid_email'
	    ],
	    'ci_captcha' => [
	      'label' => 'Captcha',
	      'rules' => 'required|trim|max_length[10]|captcha_ci'
	    ]
	  ]
	];

	public function __construct ()
	{
		$this->modelLogin = new \App\Models\Login();

		$this->builder = db_connect()->table( 'user' );

		helper( [ 'array', 'cookie' ] );

		$this->NknConfig = new \Config\Nkn();
	}

	/**
	 * @return self
	 */
	public function login () : self
	{
		$this->typeChecker( 'login' );

		return $this;
	}

	public function logout ()
	{
		delete_cookie( $this->NknConfig::NKNck );
		Services::session()->remove( $this->NknConfig::NKNss );
	}

	/**
	 * @return self
	 */
	public function forgetPass () : self
	{
		$this->typeChecker( 'forgot_password' );

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

	/** Set throttle config and get was_limited_one */
	private function getConfig () : Model
	{
		$config = $this->modelLogin->config(
			$this->NknConfig::throttle[ 'type' ],
			$this->NknConfig::throttle[ 'limit_one' ],
			$this->NknConfig::throttle[ 'limit' ],
			$this->NknConfig::throttle[ 'timeout' ]
		);

		$this->response[ 'show_captcha' ] = $this->modelLogin->was_limited_one();

		return $config;
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( false === $this->isLogged() ) return false;

		if ( empty( $key ) )
		{
			return Services::session()->get( $this->NknConfig::NKNss );
		}
		else
		{
			$userData = Services::session()->get( $this->NknConfig::NKNss );

			return $userData[ $key ] ?? dot_array_search( $key, $userData );
		}
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
	public function isLogged ( bool $checkCookie = false ) : bool
	{
		if ( ! get_cookie( $this->NknConfig::NKNck ) && ! Services::session()->get( $this->NknConfig::NKNss ) )
		{
			return false;
		}
		else if ( Services::session()->get( $this->NknConfig::NKNss ) )
		{
			return true;
		}
		else if ( ! $checkCookie )
		{
			return false;
		}

		if ( ! $cookie = get_cookie( $this->NknConfig::NKNck ) ) return false;

		$exp = explode( '-', $cookie );

		if ( empty( $exp[0] ) || empty( $exp[1] ) ) return false;

		$userID = hex2bin( $exp[1] );

		$user = $this->builder
		->select( 'cookie_token,status,last_login' )
		->where( [ 'id' => $userID ] )
		->get(1)
		->getRowArray();

		if ( ! $user ) return false;

		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) ) {
			$this->response[ 'banned' ] = $user[ 'status' ] == 'banned' ? true : false;
			$this->response[ 'inactive' ] = $user[ 'status' ] == 'inactive' ? true : false;

			return false;
		}

		$cToken = password_verify( $user[ 'cookie_token' ], $exp[0] );
		$cIP = $user[ 'last_login' ] == Services::request()->getIPAddress();

		if ( false === $cToken || false === $cIP ) return false;

		$selectQuery = [
			'User.id',
			'User.username',
			'User.password',
			'User.email',
			'User.status',
			'User.created_at',
			'User.updated_at',
			'user_group.id as group_id',
			'user_group.name as group_name',
			'user_group.permission'
		];

		$userData = $this->builder
		->select( implode( ',', $selectQuery ) )
		->join( 'user_group', 'user_group.id = User.group_id' )
		->where( [ 'User.id' => $userID ] )
		->get(1)
		->getRowArray();

		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );

		Services::session()->set( $this->NknConfig::NKNss, $userData );

		return true;
	}

	public function asObject () : object
	{
		return (object) $this->response;
	}

	public function asArray () : array
	{
		return $this->response;
	}

	/**
	 * @param string $type login | forgot_password
	 * @return void|throw
	 */
	private function typeChecker ( $type = 'login' )
	{
		if ( ! in_array( $type, [ 'login', 'forgot_password' ] ) ) {
			throw new \Exception( 'Type must be in [login or forgot_password]', 1 );
		}

		$requestType = ( $type === 'login' ) ? 'password' : 'email';
		$isNullUsername = is_null( Services::request()->getPostGet( 'username' ) );
		$isNullType = is_null( Services::request()->getPostGet( $requestType ) );

		$hasRequest = ! $isNullUsername && ! $isNullType;

		if ( true === $this->isLogged( true ) )
		{
			if ( $type === 'forgot_password' )
			$this->response[ 'forgot_password_denny' ] = true;

			else
			$this->response[ 'success' ] = true;
		}
		else if ( $was_limited = $this->getConfig()->was_limited() )
		{
			$this->response[ 'was_limited' ] = $was_limited;
		}
		else if ( false === $hasRequest )
		{
			$this->response[ 'load_view' ] = true;
		}
		else
		{
			$this->response = ( $type === 'login' )
			? $this->loginValidate()
			: $this->forgetValidate();
		}
	}

	/** Validation form login and set session & cookie */
	private function loginValidate () : array
	{
		$type = $this->modelLogin->was_limited_one()
		? 'login_with_captcha' : 'login';

		Services::Validation()
		->withRequest( Services::request() )
		->setRules( $this->rules[ $type ] );

		if ( false === Services::Validation() ->run() )
		return $this->incorrectInfo();

		$userData = $this->builder
		->select( implode( ',', $this->loginColumns() ) )
		->join( 'user_group', 'user_group.id = user.group_id' )
		->where( [ 'username' => Services::request()->getPostGet( 'username' ) ] )
		->get()
		->getRowArray();

		if ( null === $userData ) return $this->incorrectInfo();

		$verifyPassword = $this->getVerifyPass(
			Services::request()->getPostGet( 'password' ),
			$userData[ 'password' ]
		);

		if ( false === $verifyPassword ) return $this->incorrectInfo();
		if ( 'active' !== $userData[ 'status' ] )
		return $this->denyStatus( $userData['status'] );

		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );

		# --- Set success to true
		$this->response[ 'success' ] = true;

		# --- Set user session
		Services::session()->set( $this->NknConfig::NKNss, $userData );

		if ( Services::request()->getPostGet( 'remember_me' ) )
		{
			$this->setRememberMe( $userData );
		}
		else if ( false === $this->loggedInUpdate( $userData[ 'id' ] ) )
		{
			log_message( 'error', "{$userData[ 'id' ]} Logged-in, but update failed" );
		}

		# --- Cleanup throttle
		$this->modelLogin->throttle_cleanup();

		return $this->response;
	}

	/** Validation form forgot password and send mail */
	private function forgetValidate () : array
	{
		$validate_group = $this->modelLogin->was_limited_one()
		? 'forgot_password_with_captcha'
		: 'forgot_password';

		Services::Validation()
		->withRequest( Services::request() )
		->setRules( $this->rules[ $validate_group ] );

		if ( false === Services::Validation()->run() ) return $this->incorrectInfo();

		$whereQuery = [
			'username' => Services::request()->getPostGet( 'username' ),
			'email' => Services::request()->getPostGet( 'email' )
		];

		$find_user = $this->builder
		->select( 'username' )
		->where( $whereQuery )
		->get()
		->getRowArray();

		if ( null === $find_user ) return $this->incorrectInfo();

		$this->response[ 'success' ] = true;

		return $this->response;
	}

	private function incorrectInfo ( bool $throttle = true ) : array
	{
		false === $throttle ?: $this->response[ 'attemps' ] = $this->modelLogin->throttle();
		$this->response[ 'load_view' ] = true;
		$this->response[ 'wrong' ] = true;

		return $this->response;
	}

	private function denyStatus (string $status, bool $throttle = true ) : array
	{
		false === $throttle ?: $this->response[ 'attemps' ] = $this->modelLogin->throttle();
		$this->response[ 'banned' ] = $status == 'banned' ? true : false;
		$this->response[ 'inactive' ] = $status == 'inactive' ? true : false;

		return $this->response;
	}

	private function setRememberMe ( array $userData ) : void
	{
		$randomKey = Encryption::createKey(8);
		$idHex = bin2hex( $userData[ 'id' ] );
		$keyHex = bin2hex( $randomKey );
		$keyHash = password_hash( $keyHex, PASSWORD_DEFAULT );
		$cookieValue = "{$keyHash}-{$idHex}";

		$updateSuccess = $this->loggedInUpdate(
			$userData[ 'id' ], [ 'cookie_token' => $keyHex ]
		);

		# Lol don't know why need set cookie after using dbQuery class
		if ( true === $updateSuccess )
		setcookie( $this->NknConfig::NKNck, $cookieValue, time()+60*60*24*7, '/' );

		else
		log_message( 'error', "{$userData[ 'id' ]} Logged::remember-me, but update failed" );
	}

	/**
	 * @return boolean|throw
	 */
	private function loggedInUpdate ( int $userId, array $data = [] )
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception( lang( 'Validation.greater_than', $errArg ), 500 );
		}

		helper( 'array' );

		if ( ! empty( $data ) && false === isAssoc( $data ) ) {
			throw new \Exception( 'Data must be associative array', 500 );
		}

		$updateData = [
			'last_login' => Services::request()->getIPAddress(),
			'last_activity' => date( 'Y-m-d H:i:s' )
		];

		$updateData = $updateData + $data;

		return $this->builder->update( $updateData, [ 'id' => $userId ], 1 );
	}

	private function loginColumns ( array $add = [] ) : array
	{
		$colum = [
			# user
			'user.id',
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