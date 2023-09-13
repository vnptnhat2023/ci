<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class Throttle
{
	use TraitSingleton;
    /*
	|--------------------------------------------------------------------------
	| Throttle
	|--------------------------------------------------------------------------
	*/
    private const THROTTLE = [
		'type' => 1,
		'captchaAttempts' => 5,
		'maxAttempts' => 10,
		'timeoutAttempts' => 1800
	];

	public object $throttle;

	public function __construct ()
	{
		$this->throttle = ( object ) self::THROTTLE;
	}
}