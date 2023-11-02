<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Throttle;

use Red2Horse\Mixins\Classes\Base\Throttle\Throttle;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function throttleInstance () : Throttle
{
    $instance = getBaseInstance( 'Throttle', true, '', 'Throttle' );
    $instance->isSupported();
    return $instance;
}

function throttleIsSupported () : bool
{
    return throttleInstance()->isSupported();
}

function throttleIncrement () : bool
{
    return throttleInstance()->increment();
}

function throttleDecrement () : bool
{
    return throttleInstance()->decrement();
}

function throttleCleanup () : void
{
    throttleInstance()->cleanup();
}

function throttleGetAttempts () : int
{
    return throttleInstance()->getAttempts();
}

function throttleGetTypes () : int
{
    return throttleInstance()->getTypes();
}

function throttleIsLimited () : bool
{
    return throttleInstance()->isLimited();
}