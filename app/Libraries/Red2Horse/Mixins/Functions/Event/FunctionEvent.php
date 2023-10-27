<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Event;

use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @var mixed $callable */
function on ( $callable, string $classNamespace ) : void
{
    getBaseInstance( 'event' )->on( $callable, $classNamespace );
}

function trigger ( string $name, ...$args ) : bool
{
    return getBaseInstance( 'event' )->trigger( $name, ...$args );
}