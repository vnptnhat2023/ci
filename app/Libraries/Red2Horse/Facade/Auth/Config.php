<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\Config\ConfigFacade;
use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class Config
{
	use TraitSingleton;
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
  	'captchaAttempts' => 5,
  	'maxAttempts' => 10,
  	'timeoutAttempts' => 1800
	];

	public string $session = self::SESSION_NAME;
	public string $cookie = self::COOKIE_NAME;
	public int $ttl = self::TIME_TO_LIFE;
	public object $throttle;

	public string $sessionSavePath = '';
	public string $sessionCookieName = 'r2h';
	public int $sessionExpiration = 0;
	public int $sessionTimeToUpdate = 0;

	/*
	|--------------------------------------------------------------------------
	| User permission
	|--------------------------------------------------------------------------
	*/
	public array $userRoute;
	public array $userPermission;

	/*
	|--------------------------------------------------------------------------
	| Adapter
	|--------------------------------------------------------------------------
	*/
	private const ADAPTER = 'CodeIgniter';

	public function adapter( string $name = 'Auth', ?string $different = null ) : string
	{
		$different = is_null( $different ) ? $name : $different;

		return '\\App\\Libraries\\Red2Horse\Adapter\\'
		. self::ADAPTER
		. "\\{$name}\\{$different}Adapter";
	}

	/*
	|--------------------------------------------------------------------------
	| construct
	|--------------------------------------------------------------------------
	*/
	public function __construct ()
	{
		$adapter = $this->adapter( 'Config' );

		if ( ! class_exists( $adapter ) ) {
			throw new \Exception( "The adapter config file: {$adapter} not found.", 403 );
		}

		$facade = ConfigFacade::getInstance( new $adapter );

		$this->userRoute = $facade->userRoute();
		$this->userPermission = $facade->userPermission();

		$this->sessionSavePath = $facade->sessionSavePath();
		$this->sessionCookieName = $facade->sessionCookieName();
		$this->sessionExpiration = $facade->sessionExpiration();
		$this->sessionTimeToUpdate = $facade->sessionTimeToUpdate();

		$this->throttle = (object) self::THROTTLE;
	}

	/*
	|--------------------------------------------------------------------------
	| Rules of validation groups
	|--------------------------------------------------------------------------
	*/
	# --- Form input name
	public const USERNAME = 'username';
	public const PASSWORD = 'password';
	public const EMAIL = 'email';
	public const CAPTCHA = 'captcha';

	# --- form input group
	public const LOGIN = 'login';
	public const LOGIN_WITH_CAPTCHA = 'login_captcha';
	public const FORGET = 'forget';
	public const FORGET_WITH_CAPTCHA = 'forget_captcha';

	# --- rule groups
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
			'captchaAttempts' => self::THROTTLE[ 'captchaAttempts' ],
			'maxAttempts' => self::THROTTLE[ 'maxAttempts' ],
			'timeoutAttempts' => self::THROTTLE[ 'timeoutAttempts' ]
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
	public function getColumString ( array $columns = [], bool $join = true ) : string
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
			'user.selector',
			'user.token',
			...$columns
		];

		if ( true === $join ) {
			# user_group
			$columns[] = 'user_group.id as group_id';
			$columns[] = 'user_group.name as group_name';
			$columns[] = 'user_group.permission';
		}

		return implode( ',', $columns );
	}
}