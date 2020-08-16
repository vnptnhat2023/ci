<?php

namespace App\Libraries\NknAuth;

class Config
{
	private const SESSION_NAME = 'oknkn';
	private const COOKIE_NAME = 'konkn';
	private const TIME_TO_LIFE = WEEK;
  private const THROTTLE = [
  	'type' => 1,
  	'limit_one' => 5,
  	'limit' => 10,
  	'timeout' => 1800
	];

	private \stdClass $config;

	public function __construct()
	{
		if ( empty( $this->config ) ) $this->makeConfig();
	}

	private function makeConfig () : void
	{
		$data = [
			'session' => Config::SESSION_NAME,
			'cookie' => Config::COOKIE_NAME,
			'ttl' => Config::TIME_TO_LIFE,
			'throttle' => (object) Config::THROTTLE
		];

		$this->config = (object) $data;
	}

	public function getConfig ( bool $reset = false ) : \stdClass
	{
		if ( true === $reset ) { $this->makeConfig(); }

		return $this->config;
	}

	public function setConfig ( array $data ) : \stdClass
	{
		$cfg = $this->config;

		$cfg ->session ??= $data[ 'session' ];
		$cfg ->cookie ??= $data[ 'cookie' ];
		$cfg ->ttl ??= $data[ 'ttl' ];

		$cfg ->throttle ->type ??= $data[ 'throttle_type '];
		$cfg ->throttle ->limit ??= $data[ 'throttle_limit '];
		$cfg ->throttle ->limit_one ??= $data[ 'throttle_limit_one '];
		$cfg ->throttle ->timeout ??= $data[ 'throttle_timeout '];

		$this->config = $cfg;

		return $this->getConfig();
	}
}