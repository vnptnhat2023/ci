<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Object;

use Red2Horse\Mixins\Interfaces\Object\CallClass___Interface;

use Red2Horse\Mixins\Traits\Object\
{
    TraitCall,
    TraitSingleton
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CallClass___ implements CallClass___Interface
{
    use TraitCall, TraitSingleton;

    public function __construct( $className = null, array $arguments )
    {
        $this->run( $className );
    }
}