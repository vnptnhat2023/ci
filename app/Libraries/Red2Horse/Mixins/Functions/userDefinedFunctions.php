<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

// use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserDefinedFunctions
{
    // use TraitSingleton;

    public function __construct ()
    {
        
    }
    

    public function __call($method, $args)
    {
        // d( $method );
    }
    
}