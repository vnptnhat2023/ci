<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle;

use Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface\ThrottleAdapterInterface;
use Red2Horse\Mixins\Classes\Sql\Model;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Model\model;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ThrottleDatabase implements ThrottleAdapterInterface
{
    use TraitSingleton;

    protected Model $model;

    public function __construct () { }

    public function isSupported () : bool
    {
        $this->model = model( 'Throttle/ThrottleModel' );
        return isset( $this->model );
    }

    public function increment ( Throttle $baseThrottle ) : bool
    {
        return true;
    }

    public function cleanup ( Throttle $baseThrottle ) : void
    {
        
    }

    public function delete ( Throttle $baseThrottle ) : bool
    {
        return true;
    }
}