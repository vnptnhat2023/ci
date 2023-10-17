<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Cookie
{
	use TraitSingleton;

	private const COOKIE_NAME = 'r2h';
	private const TIME_TO_LIFE = 604800;

	public string $cookie = self::COOKIE_NAME;
	public int $ttl = self::TIME_TO_LIFE;

	private function __construct () {}
}