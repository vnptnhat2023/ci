<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Instance;

use Red2Horse\Mixins\Classes\Registry\RegistryClass;

use function Red2Horse\Mixins\Functions\NS\registryNamespace;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function RegInstance ( string $state ) : object
{
    $state = registryNamespace( $state );
    return $state::selfInstance();
}

/**
 * @return mixed array|null
 */
function instanceData ( string $classNamespace, string $state = 'RegistryClass' )
{
    return RegInstance( $state ) ->InstanceData( $classNamespace );
}

/**
 * @param string $key
 * @return mixed array|object ( call )
 * @throws $th
 */
function getClass ( string $classNamespace, string $key = '', string $state = 'RegistryClass' )
{
    return RegInstance( $state ) ->getClass( $classNamespace, $key );
}

function hasClass ( string $classNamespace, string $state = 'RegistryClass' ) : bool
{
    return RegInstance( $state ) ->hasClass( $classNamespace );
}

/** @param mixed $value */
function setClass ( string $classNamespace, $value, bool $override = false, string $state = 'RegistryClass' ) : bool
{
    return RegInstance( $state ) ->setClass( $classNamespace, $value, $override );
}

function delClass ( string $classNamespace, string $state = 'RegistryClass' ) : bool
{
    return RegInstance( $state ) ->delClass( $classNamespace );
}

function _debugInfo( string $state ) : array
{
    return RegInstance( $state)->_debugInfo();
}