<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse;

class Config
{
	private const SESSION_NAME = 'r2h';
	private const COOKIE_NAME = 'r2h';
	private const TIME_TO_LIFE = 604800;
  private const THROTTLE = [
  	'type' => 1,
  	'limit_one' => 5,
  	'limit' => 10,
  	'timeout' => 1800
	];

	private const DEFAULT_ADAPTER = 'CodeIgniter';

	public string $session = self::SESSION_NAME;
	public string $cookie = self::COOKIE_NAME;
	public int $ttl = self::TIME_TO_LIFE;
	public object $throttle;

	public string $authAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::DEFAULT_ADAPTER . '\\Auth\AuthAdapter';
	public string $mailAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::DEFAULT_ADAPTER . '\\Mail\MailAdapter';
	public string $validationAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::DEFAULT_ADAPTER . '\\Validation\ValidationAdapter';
	public string $databaseAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::DEFAULT_ADAPTER . '\\Database\DatabaseAdapter';
	public string $cacheAdapter = '\App\Libraries\Red2Horse\Adapter\\' . self::DEFAULT_ADAPTER . '\\Cache\CacheAdapter';

	public function __construct ()
	{
		$this->throttle = (object) self::THROTTLE;
	}

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
}