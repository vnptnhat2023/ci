<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class BaseConfig
{
	use TraitSingleton;

	public bool $useRememberMe = false;
	public bool $useThrottle = false;// ->captcha
	public bool $useMultiLogin = false;
}