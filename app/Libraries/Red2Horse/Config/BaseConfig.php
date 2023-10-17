<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class BaseConfig
{
	use TraitSingleton;

	public bool $useRememberMe = false;
	public bool $useMultiLogin = false;

	private function __construct () {}
}