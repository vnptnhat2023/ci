<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class Validation
{
	use TraitSingleton;

	public static $username = 'username';
	public static $password = 'password';
	public static $email = 'email';
	public static $captcha = 'captcha';
}