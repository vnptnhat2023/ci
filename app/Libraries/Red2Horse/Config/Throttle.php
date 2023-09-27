<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Throttle
{
	use TraitSingleton;

    private const THROTTLE = [
		'type' => 1,
		'captchaAttempts' => 5,
		'maxAttempts' => 10,
		'timeoutAttempts' => 1800
	];

	public object $throttle;

	public bool $useThrottle = true;

	private function __construct ()
	{
		$this->throttle = ( object ) self::THROTTLE;
	}

	public function reInit () : void
	{
		getComponents( 'throttle' )->init();
	}
}