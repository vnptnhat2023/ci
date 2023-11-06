<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Throttle
{
	use TraitSingleton;

	public 		bool 		$useThrottle 		= false;
	public 		string 		$currentAdapter  	= 'database';
	public 		int 		$attempt		 	= 0;
	public 		int 		$type		 	 	= 0;
	public 		int 		$typeAttempt	 	= 3;
	public 		int 		$typeLimit		 	= 5;
	public 		int 		$timeout		 	= 1800;
	public 		string 		$cacheName 			= 'throttle_cache_name';
	public 		array 		$adapters 			= [
        'cache'     =>	\Red2Horse\Mixins\Classes\Base\Throttle\ThrottleCache::class,
        'database'  =>	\Red2Horse\Mixins\Classes\Base\Throttle\ThrottleDatabase::class
    ];

	private function __construct ()
	{
		$this->reInit();
	}

	public function reInit ()
	{
		$this->cacheName = getConfig( 'cache' )->getCacheName( $this->cacheName );
	}
	
}