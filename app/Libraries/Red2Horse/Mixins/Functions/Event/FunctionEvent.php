<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Event;

use function Red2Horse\Mixins\Functions\Instance\BaseInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @var mixed $callable */
function on ( $callable, string $classNamespace ) : void
{
    BaseInstance( 'event' )->on( $callable, $classNamespace );
}

function trigger ( string $name, ...$args ) : bool
{
    return BaseInstance( 'event' )->trigger( $name, ...$args );
}