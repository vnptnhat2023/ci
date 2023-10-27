<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;

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

	/** NEW */
	public bool $useThrottle = true;

	public array $adapters = [
        'cache'     => \Red2Horse\Mixins\Classes\Base\Throttle\ThrottleCache::class,
        'database'  => \Red2Horse\Mixins\Classes\Base\Throttle\ThrottleDatabase::class
    ];
	public 			string 		$currentAdapter  = 'cache';
	private 		int 		$attempt		 = 0;
	private 		int 		$type		 	 = 1;
	private 		int 		$limitType		 = 1;
	private 		int 		$limit		 	 = 5;
	private 		int 		$timeout		 = 1800;

	public string $cacheName = '';
	/** END NEW */

	private function __construct ()
	{
		$this->throttle = ( object ) self::THROTTLE;#
	}

	public function reInit () : void
	{
		getComponents( 'throttle' )->init();
		// getInstance( 'throttle' )->init();
	}
}