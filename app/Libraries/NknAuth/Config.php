<?php

namespace App\Libraries\NknAuth;

use CodeIgniter\Config\BaseConfig;

class Config extends BaseConfig
{
	/** NknAuth session-name */
	public const NKNss = 'oknkn';

	/** NknAuth cookie-name */
	public const NKNck = 'konkn';
	public const NKNckTtl = WEEK;

  public const throttle = [
  	'type' => 1,
  	'limit_one' => 5,
  	'limit' => 10,
  	'timeout' => 1800
	];

	private static object $userConfig;

	/**
	 * Make an anonymous config property class
	 */
	private function makeUserConfig() : void
	{
		if ( empty( self::$userConfig ) ) {
			self::$userConfig = new class {
				public string $session = Config::NKNss;
				public string $cookie = Config::NKNck;
				public int $ttl = Config::NKNckTtl;
				public object $throttle;

				public function __construct()
				{
					$this->throttle = new class {
						public int $type = Config::throttle[ 'type' ];
						public int $limit = Config::throttle[ 'limit' ];
						public int $limit_one = Config::throttle[ 'limit_one' ];
						public int $timeout = Config::throttle[ 'timeout' ];
					};
				}
			};
		}
	}

	/**
	 * @param bool Received default config
	 * @return Nkn::$userConfig
	 */
	public function getConfig ( bool $default = false ) : object
	{
		if ( empty( self::$userConfig ) ) $this->makeUserConfig();

		if ( true === $default ) {
			$this->setConfig([
				'session' => self::NKNss,
				'cookie' => self::NKNck,
				'cookie_ttl' => self::NKNckTtl,

				'throttle_type' => self::throttle['type'],
				'throttle_limit' => self::throttle['limit'],
				'throttle_limit_one' => self::throttle['limit_one'],
				'throttle_timeout' => self::throttle['timeout'],
			]);
		}

		return self::$userConfig;
	}

	public function setConfig ( array $data ) : object
	{
		if ( empty( self::$userConfig ) ) {
			$this->makeUserConfig();
		}

		self::$userConfig->session ??= $data[ 'session' ];
		self::$userConfig->cookie ??= $data[ 'cookie' ];
		self::$userConfig->ttl ??= $data[ 'ttl' ];

		self::$userConfig->throttle->type ??= $data[ 'throttle_type '];
		self::$userConfig->throttle->limit ??= $data[ 'throttle_limit '];
		self::$userConfig->throttle->limit_one ??= $data[ 'throttle_limit_one '];
		self::$userConfig->throttle->timeout ??= $data[ 'throttle_timeout '];

		return self::$userConfig;
	}
}