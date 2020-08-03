<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Encryption\Encryption;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\Validation;
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
	/**
	 * Cookie name
	 * @var string $NKNck
	 */
	private string $NKNck;

	/**
	 * Session name
	 * @var string $NKNss
	 */
	private string $NKNss;

	private array $throttle_config = [
		'type' => 1,
		'limit_one' => 5,
		'limit' => 10,
		'timeout' => 30
	];

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
	 * @var \CodeIgniter\Session\Session $session
	 */
	protected Session $session;

	protected Validation $validation;

	/**
	 * @var \CodeIgniter\HTTP\IncomingRequest $request
	 */
	protected Request $request;

	/**
	 * @var \App\Models\Login $throttle
	 */
	protected Model $throttle;

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
		$this->session = Services::session();
		$this->request = Services::request();
		$this->throttle = new \App\Models\Login();
		$this->builder = db_connect()->table( 'User' );

		helper( [ 'array', 'cookie' ] );
		$NknConfig = new \Config\Nkn();

		# --- Adding config
		$this->NKNss = $NknConfig->NKNss;
		$this->NKNck = $NknConfig->NKNck;
		$this->throttle_config = $NknConfig->throttle;
	}

	/**
	 * @return self
	 */
	public function login () : self
	{
		$this->typeChecker( 'login' );

		return $this;
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

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( false === $this->isLogged() ) return false;

		if ( empty( $key ) )
		{
			return $this->session->get( $this->NKNss );
		}
		else
		{
			$userData = $this->session->get( $this->NKNss );

			return $userData[ $key ] ?? dot_array_search( $key, $userData );
		}
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

	public function getHashPass ( string $password, int $cost = 12 ) : string
  {
    $salt = password_hash( $password, PASSWORD_BCRYPT, [ 'cost' => $cost ] );
    return $salt;
  }

  public function getVerifyPass ( string $password, string $salt ) : bool
  {
  	return password_verify( $password, $salt );
  }

	public function logout ()
	{
		delete_cookie( $this->NKNck );
		$this->session->remove( $this->NKNss );
	}

	/**
	* Check cookie, session: if have cookie will set session
	* @return boolean
	*/
	public function isLogged ( bool $checkCookie = false ) : bool
	{
		if ( ! get_cookie( $this->NKNck ) && ! $this->session->get( $this->NKNss ) )
		{
			return false;
		}
		else if ( $this->session->get( $this->NKNss ) )
		{
			return true;
		}
		else if ( ! $checkCookie )
		{
			return false;
		}

		if ( ! $cookie = get_cookie( $this->NKNck ) ) return false;

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
		$cIP = $user[ 'last_login' ] == $this->request->getIPAddress();

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

		$this->session->set( $this->NKNss, $userData );

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
		$isNullUsername = is_null( $this->request->getPostGet( 'username' ) );
		$isNullType = is_null( $this->request->getPostGet( $requestType ) );

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
			$this->validation = service( 'validation' );

			$this->response = ( $type === 'login' )
			? $this->loginValidate()
			: $this->forgetValidate();
		}
	}

	/** Set throttle config and get was_limited_one return boolean method */
	private function getConfig () : Model
	{
		$config = $this->throttle->config(
			$this->throttle_config[ 'type' ],
			$this->throttle_config[ 'limit_one' ],
			$this->throttle_config[ 'limit' ],
			$this->throttle_config[ 'timeout' ]
		);

		$this->response[ 'show_captcha' ] = $this->throttle->was_limited_one();

		return $config;
	}

	/** Validation form login and set session & cookie */
	private function loginValidate () : array
	{
		$validate_group = $this->throttle->was_limited_one()
		? 'login_with_captcha'
		: 'login';

		$this->validation
		->withRequest( $this->request )
		->setRules( $this->rules[ $validate_group ] );

		if ( false === $this->validation->run() ) {
			$this->response[ 'attemps' ] = $this->throttle->throttle() + 1;
			$this->response[ 'load_view' ] = true;
			$this->response[ 'wrong' ] = true;

			return $this->response;
		}

		$selectQuery = [
			'User.id',
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
		->where( [ 'username' => $this->request->getPostGet( 'username' ) ] )
		->get()
		->getRowArray();

		if ( null === $userData ) {
			$this->response[ 'attemps' ] = $this->throttle->throttle() + 1;
			$this->response[ 'load_view' ] = true;
			$this->response[ 'wrong' ] = true;

			return $this->response;
		}

		$verifyPassword = $this->getVerifyPass(
			$this->request->getPostGet( 'password' ),
			$userData[ 'password' ]
		);

		if ( false === $verifyPassword ) {
			$this->response[ 'attemps' ] = $this->throttle->throttle() + 1;
			$this->response[ 'load_view' ] = true;
			$this->response[ 'wrong' ] = true;

			return $this->response;
		}

		if ( $userData[ 'status' ] !== 'active' )
		{
			$this->response[ 'attemps' ] = $this->throttle->throttle() + 1;
			$this->response[ 'banned' ] = $userData[ 'status' ] == 'banned' ? true : false;
			$this->response[ 'inactive' ] = $userData[ 'status' ] == 'inactive' ? true : false;

			return $this->response;
		}

		$userData[ 'permission' ] = json_decode( $userData[ 'permission' ] );
		$this->response[ 'success' ] = true;
		# --- Set user session
		$this->session->set( $this->NKNss, $userData);

		if ( $this->request->getPostGet( 'remember_me' ) ) {
			$randomKey = Encryption::createKey(8);
			$idHex = bin2hex( $userData[ 'id' ] );
			$keyHex = bin2hex( $randomKey );
			$keyHash = password_hash( $keyHex, PASSWORD_DEFAULT );
			$cookieValue = "{$keyHash}-{$idHex}";

			$createToken = $this->builder
			->where( 'id', $userData[ 'id' ] )
			->update( [ 'cookie_token' => $keyHex ] );

			if ( $createToken ) {
				setcookie( $this->NKNck, $cookieValue, time()+60*60*24*7, '/' );
			}
		}

		$this->throttle->throttle_cleanup();

		return $this->response;
	}

	/** Validation form forgot password and send mail */
	private function forgetValidate () : array
	{
		$validate_group = $this->throttle->was_limited_one()
		? 'forgot_password_with_captcha'
		: 'forgot_password';

		$this->validation
		->withRequest( $this->request )
		->setRules( $this->rules[ $validate_group ] );

		if ( false === $this->validation->run() ) {
			die( 'not passed' );
			$this->response[ 'attemps' ] = $this->throttle->throttle() + 1;
			$this->response[ 'load_view' ] = true;
			$this->response[ 'wrong' ] = true;

			return $this->response;
		}

		// die( 'passed' );
		$find_user = $this->builder
		->select( 'username' )
		->where( [
			'username' => $this->request->getPostGet( 'username' ),
			'email' => $this->request->getPostGet( 'email' )
		] )
		->get();

		if ( ! $user = $find_user->getRowArray() )
		{
			$this->response[ 'attemps' ] = $this->throttle->throttle() + 1;
			$this->response[ 'load_view' ] = true;
			$this->response[ 'wrong' ] = true;
		}
		else
		{
			$this->response[ 'success' ] = true;
		}

		return $this->response;
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

/* End of file my_auth.php */
/* Location: ./app/libraries/My_auth.php */