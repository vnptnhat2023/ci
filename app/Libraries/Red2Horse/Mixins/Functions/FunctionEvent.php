<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/**
 * @param mixed $callable [ class|instance ], callable
 * @return mixed
 */
function on ( string $name, $callable, int $priority ) : void
{
    getComponents( 'event' )->on( $name, $callable, $priority );
}

function trigger ( string $name, ...$args ) : bool
{
    return getComponents( 'event' )->trigger( $name, ...$args );
}