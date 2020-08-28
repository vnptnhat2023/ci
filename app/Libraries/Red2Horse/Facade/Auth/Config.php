<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

class Config
{
	/*
	|--------------------------------------------------------------------------
	| Session & Cookie & Throttle
	|--------------------------------------------------------------------------
	*/
	private const SESSION_NAME = 'r2h';
	private const COOKIE_NAME = 'r2h';
	private const TIME_TO_LIFE = 604800;
  private const THROTTLE = [
  	'type' => 1,
  	'limit_one' => 5,
  	'limit' => 10,
  	'timeout' => 1800
	];

	public string $session = self::SESSION_NAME;
	public string $cookie = self::COOKIE_NAME;
	public int $ttl = self::TIME_TO_LIFE;
	public object $throttle;

	/*
	|--------------------------------------------------------------------------
	| Lengths of the random_bytes function
	|--------------------------------------------------------------------------
	*/
	public int $randomBytesLength = 8;

	/*
	|--------------------------------------------------------------------------
	| Adapter
	|--------------------------------------------------------------------------
	*/
	private const ADAPTER = 'CodeIgniter';

	public string $authAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::ADAPTER . '\\Auth\AuthAdapter';
	public string $mailAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::ADAPTER . '\\Mail\MailAdapter';
	public string $validationAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::ADAPTER . '\\Validation\ValidationAdapter';
	public string $databaseAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::ADAPTER . '\\Database\DatabaseAdapter';
	public string $cacheAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::ADAPTER . '\\Cache\CacheAdapter';

	/*
	|--------------------------------------------------------------------------
	| constructor
	|--------------------------------------------------------------------------
	*/
	public function __construct ()
	{
		$this->throttle = (object) self::THROTTLE;
	}

	/*
	|--------------------------------------------------------------------------
	| Rules of validation groups
	|--------------------------------------------------------------------------
	*/
	public const USERNAME = 'username';
	public const PASSWORD = 'password';
	public const EMAIL = 'email';
	public const CAPTCHA = 'captcha';
	# --- $ruleGroup key name
	public const LOGIN = 'login';
	public const LOGIN_WITH_CAPTCHA = 'login_captcha';
	public const FORGET = 'forget';
	public const FORGET_WITH_CAPTCHA = 'forget_captcha';

	public array $ruleGroup = [
		self::LOGIN => [
			self::USERNAME,
			self::PASSWORD
		],
		self::LOGIN_WITH_CAPTCHA => [
			self::USERNAME,
			self::PASSWORD,
			self::CAPTCHA
		],

		self::FORGET => [
			self::USERNAME,
			self::EMAIL
		],
		self::FORGET_WITH_CAPTCHA => [
			self::USERNAME,
			self::EMAIL,
			self::CAPTCHA
		]
	];

	/*
	|--------------------------------------------------------------------------
	| Reset all config to default
	|--------------------------------------------------------------------------
	*/
	public function reset () : void
	{
		$throttle = [
			'type' => self::THROTTLE[ 'type' ],
			'limit_one' => self::THROTTLE[ 'limit_one' ],
			'limit' => self::THROTTLE[ 'limit' ],
			'timeout' => self::THROTTLE[ 'timeout' ]
		];

		$this->session = self::SESSION_NAME;
		$this->cookie = self::COOKIE_NAME;
		$this->ttl = self::TIME_TO_LIFE;
		$this->throttle = (object) $throttle;
	}

	/*
	|--------------------------------------------------------------------------
	| SQL syntax select user columns names
	|--------------------------------------------------------------------------
	*/
	public function getStringColum ( array $moreColumns = [] ) : string
	{
		$columns = [
			# user
			'user.id',
			'user.username',
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
			...$moreColumns
		];

		return implode( ',', $columns );
	}
}