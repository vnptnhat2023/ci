<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @return object $state */
function RegInstance( string $state ) : object
{
    $state = registryNamespace( $state );
    return $state::selfInstance();
}

/**
 * @return mixed array|null
 */
function instanceData ( string $name, string $state = 'RegistryClass' )
{
    return RegInstance( $state ) ->InstanceData( $name );
}

/**
 * @param string $key
 * @return mixed array|object ( call )
 * @throws $th
 */
function getClass ( string $name, string $key = '', string $state = 'RegistryClass' )
{
    return RegInstance( $state ) ->getClass( $name, $key );
}

function hasClass ( string $name, string $key = '', string $state = 'RegistryClass' )
{
    return RegInstance( $state ) ->hasClass( $name, $key );
}

/** @param mixed $value */
function setClass ( string $name, $value, bool $override = false, string $state = 'RegistryClass' ) : bool
{
    return RegInstance( $state ) ->setClass( $name, $value, $override );
}

function delClass ( string $name, string $state = 'RegistryClass' ) : bool
{
    return RegInstance( $state ) ->delClass( $name );
}

function _debugInfo( string $state ) : array
{
    return RegInstance( $state)->_debugInfo();
}