<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface;

use Red2Horse\Mixins\Classes\Base\Throttle\Throttle;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

interface ThrottleAdapterInterface
{
    public function isSupported () : bool;

    public function increment ( Throttle $baseThrottle ) : bool;

    public function cleanup ( Throttle $baseThrottle ) : void;

    public function delete ( Throttle $baseThrottle ) : bool;
}