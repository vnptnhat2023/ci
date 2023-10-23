<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle;

use Red2Horse\Mixins\Classes\Base\Throttle\Throttle;
use Red2Horse\Facade\Cache\CacheFacade;
use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleAdapterInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ThrottleCache implements ThrottleAdapterInterface
{
    use TraitSingleton;

    public function __construct () {}

    public function isSupported () : bool
    {
        return $this->getCache()->isSupported();
    }

    public function increment ( Throttle $baseThrottle ) : bool
    {
        return $this->handle( $baseThrottle );
    }

    public function cleanup ( Throttle $baseThrottle ) : void
    {
        $this->handle( $baseThrottle, true );
    }

    public function delete ( Throttle $baseThrottle ) : bool
    {
        return $this->getCache()->delete( $baseThrottle->cacheName );
    }

    private function handle ( Throttle $baseThrottle, bool $reset = false ) : bool
    {
        $setData = [];

        if ( $reset || empty( $this->getCache() ->get( $baseThrottle->cacheName ) ) )
		{
			$setData[ 'throttle_attempt' ] = 1;
		}
        else
        {
            $setData[ 'throttle_attempt' ] += 1;
        }

		$isset = $this->getCache() ->set( $baseThrottle->cacheName, $setData, $baseThrottle->timeout );

		return $isset;
    }
    

    private function getCache() : CacheFacade
    {
        return getComponents( 'cache' );
    }
}