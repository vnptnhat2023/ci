<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Event;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getClass;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @var mixed $callable */
function on ( $callable, ?string $classNamespace = null ) : void
{
    getBaseInstance( 'event' )->on( $callable, $classNamespace );
}

function trigger ( string $name, ...$args ) : bool
{
    return getBaseInstance( 'event' )->trigger( $name, ...$args );
}

/**
 * @return <string, ?mixed> string key: "$name"
 */
function eventReturnedData ( string $name, ...$args ) : array
{
    if ( trigger( $name, $args ) )
    {
        $returned = getClass( $name, '', 'RegistryEventClass' );
    }

    $return = [ $name => $returned ?? null ];

    return $return;
}

/**
 * @return mixed false : false
 */
function eventReturnedDataWithUse ( string $name, ...$args )
{
    $eventConfig = getConfig( 'event' );
    $data        = [];
    
    if ( $eventConfig->useBefore )
    {
        $beforeName = $eventConfig->getPrefixNamed( $eventConfig->beforePrefix, $name );

        if ( trigger( $beforeName, $args ) )
        {
            $data[ 'before' ] = getClass( $beforeName, '', 'RegistryEventClass' );
        }
    }

    if ( $eventConfig->useAfter )
    {
        $afterName = $eventConfig->getPrefixNamed( $eventConfig->afterPrefix, $name );

        if ( trigger( $afterName, $args ) )
        {
            $data[ 'after' ] = getClass( $afterName, '', 'RegistryEventClass' );
        }
    }

    $name = $eventConfig->getPrefixNamed( $eventConfig->afterPrefix, $name );
    if ( trigger( $name, $args ) )
    {
        $data[ 'data' ] = getClass( $name, '', 'RegistryEventClass' );
    }

    return [] !== $data ? $data : false;
}