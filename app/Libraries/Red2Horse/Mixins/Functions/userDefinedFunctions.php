<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserDefinedFunctions
{
    public function __construct () {}
    
    public function message_show_captcha_condition ( int $attempt, int $type ) : bool
    {
        return $attempt >= 3;
    }

    public function authentication_show_captcha_condition ( int $attempt, int $type ) : bool
    {
        return $attempt >= 3;
    }
    
    public function resetpassword_show_captcha_condition ( int $attempt, int $type ) : bool
    {
        return $attempt >= 3;
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