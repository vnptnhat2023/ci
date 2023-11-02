<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

interface ThrottleInterface
{
    public      function     cleanup         () : void;
    public      function     decrement       () : bool;
    public      function     increment       () : bool;
    public      function     isSupported     () : bool;
    public      function     isLimited       () : bool;
    public      function     getAttempts     () : int;
    public      function     getTypes        () : int;
}