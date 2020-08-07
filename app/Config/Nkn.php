<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Nkn extends BaseConfig
{
	# --- Default PAGE?
	# --- Default PAGE TYPE? "extension needed multiple pages and subPage"
	# --- Page TYPE, title, slug, id?
	# --- Relation using?
	# --- * Controller Or Content?

	/** NknAuth session-name */
	public const NKNss = 'oknkn';

	/** NknAuth cookie-name */
	public const NKNck = 'konkn';
	public const NKNckTtl = WEEK;

  public const throttle = [
  	'type' => 1,
  	'limit_one' => 4,
  	'limit' => 10,
  	'timeout' => 30
	];

	private static object $userConfig;

	/**
	 * Make an anonymous config property class
	 */
	private function makeUserConfig() : void
	{
		if ( empty( self::$userConfig ) ) {
			self::$userConfig = new class {
				public string $sessionName = Nkn::NKNss;
				public string $cookieName = Nkn::NKNck;
				public int $cookieTTL = Nkn::NKNckTtl;
				public object $throttle;

				public function __construct()
				{
					$this->throttle = new class {
						public int $type = Nkn::throttle[ 'type' ];
						public int $limit = Nkn::throttle[ 'limit' ];
						public int $limit_one = Nkn::throttle[ 'limit_one' ];
						public int $timeout = Nkn::throttle[ 'timeout' ];
					};
				}
			};
		}
	}

	public function getConfig () : object
	{
		if ( empty( self::$userConfig ) ) {
			$this->makeUserConfig();
		}

		return self::$userConfig;
	}

	public function setConfig ( array $data ) : object
	{
		if ( empty( self::$userConfig ) ) {
			$this->makeUserConfig();
		}

		self::$userConfig->sessionName = $data[ 'session' ] ?? Nkn::NKNss;
		self::$userConfig->cookieName = $data[ 'cookie' ] ?? Nkn::NKNck;
		self::$userConfig->cookieTTL = $data[ 'cookie_ttl' ] ?? Nkn::NKNckTtl;

		self::$userConfig->throttle->type = $data[ 'throttle_type '] ?? Nkn::throttle['type'];
		self::$userConfig->throttle->limit = $data[ 'throttle_limit '] ?? Nkn::throttle['limit'];
		self::$userConfig->throttle->limit_one = $data[ 'throttle_limit_one '] ?? Nkn::throttle['limit_one'];
		self::$userConfig->throttle->timeout = $data[ 'throttle_timeout '] ?? Nkn::throttle['timeout'];

		return self::$userConfig;
	}
}