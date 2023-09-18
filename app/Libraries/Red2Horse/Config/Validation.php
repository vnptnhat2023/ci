<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Validation
{
	use TraitSingleton;

	public static $username = 'username';
	public static $password = 'password';
	public static $email = 'email';
	public static $captcha = 'captcha';
}