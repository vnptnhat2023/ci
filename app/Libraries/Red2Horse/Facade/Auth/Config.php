<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\{
	Facade\Config\ConfigFacade,
	Mixins\Traits\TraitSingleton
};

/**
 * 	Facade, adapter
 * */
class Config
{
	use TraitSingleton;

	/*
	|--------------------------------------------------------------------------
	| General
	|--------------------------------------------------------------------------
	*/
	public bool $useRememberMe = true;
	public bool $useMultiLogin = false;

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
	| User Authorization
	|--------------------------------------------------------------------------
	*/
	public array $userRouteGates;
	public array $userPermission;
	public array $userRole;

	public string $adminGate;
	public string $adminPermission;
	public string $adminRole;
	public array $sessionKey;

	/*
	|--------------------------------------------------------------------------
	| Adapter
	|--------------------------------------------------------------------------
	*/
	private const ADAPTER = 'CodeIgniter';

	public function adapter( string $name = 'Auth', ?string $diff = null ) : string
	{
		$diff = $diff ?? $name;
		return 'Red2Horse\\Adapter\\' . self::ADAPTER . "\\{$name}\\{$diff}Adapter";
	}

	public function facade( string $name = 'Auth', ?string $diff = null ) : string
	{
		$diff = $diff ?? $name;
		return "Red2Horse\\Facade\\{$name}\\{$diff}Facade";
	}

	/*
	|--------------------------------------------------------------------------
	| construct
	|--------------------------------------------------------------------------
	*/
	public function __construct ()
	{
		$adapterComponents = $this->adapter( 'Config' );

		if ( ! class_exists( $adapterComponents ) ) {
			throw new \Exception( "The adapter : {$adapterComponents} not found.", 403 );
		}

		$facade = ConfigFacade::getInstance( new $adapterComponents );
		$this->sessionKey = $facade->getSessionKey();

		# --- Authorization
		$this->userRouteGates = $facade->userRouteGates();
		$this->userPermission = $facade->userPermission();
		$this->userRole = $facade->userRole();

		$this->adminGate = $facade::ADMINISTRATOR_GATE;
		$this->adminPermission = $facade::ADMINISTRATOR_PERMISSION;
		$this->adminRole = $facade::ADMINISTRATOR_ROLE;

		# --- Session
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
	| Reset all config to the default
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
			$columns[] = 'user_group.role';
		}

		return implode( ',', $columns );
	}
}