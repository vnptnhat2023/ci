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
    

    public function __call( $methodName, $arguments )
    {
        // if ( $methodName == 'r2h_after_get_message' )
        // {
        //     $arguments[0]->message->success[] = '123';
        //     $arguments[0]->message->success[] = '321';
        //     $arguments[0]->message->success[] = '456';
        // }
        // d( $methodName, $arguments );
    }
    
}