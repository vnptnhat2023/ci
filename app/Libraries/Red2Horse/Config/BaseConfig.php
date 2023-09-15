<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class BaseConfig
{
	use TraitSingleton;

	public bool $useRememberMe = true;
	public bool $useMultiLogin = false;

	public function __construct ()
	{
	}
}