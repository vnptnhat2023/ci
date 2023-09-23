<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes;

use Red2Horse\Mixins\Interfaces\CallClass___Interface;
use Red2Horse\Mixins\Traits\{
    TraitCall,
    TraitSingleton
};

use function Red2Horse\Mixins\Functions\getConfig;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CallClass___ implements CallClass___Interface
{
    use TraitCall, TraitSingleton;

    public function __construct( $className = null, array $arguments )
    {
        $this->run( $className );
    }
}