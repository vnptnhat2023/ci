<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base\Throttle\ThrottleInterface;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

interface ThrottleAdapterInterface
{
    public function init ( array $props ) : int;

    public function isSupported () : bool;

    public function increment () : bool;

    public function decrement () : bool;

    public function cleanup () : void;

    public function delete () : bool;
}