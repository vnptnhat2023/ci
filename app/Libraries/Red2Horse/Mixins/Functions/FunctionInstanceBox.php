<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Mixins\Classes\Registry\RegistryClass as RegClass;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access not allowed.' );

/** @return object $state */
function RegInstance( string $state ) : object
{
    return $state::selfInstance();
}

/**
 * @return mixed array|null
 */
function instanceData ( string $classNamespace, string $state = RegClass::class )
{
    return RegInstance( $state ) ->InstanceData( $classNamespace );
}

/**
 * @param string $key
 * @return mixed array|object ( call )
 * @throws $th
 */
function getClass ( string $classNamespace, string $key = '', string $state = RegClass::class )
{
    return RegInstance( $state ) ->getClass( $classNamespace, $key );
}

function hasClass ( string $classNamespace, string $key = '', string $state = RegClass::class )
{
    return RegInstance( $state ) ->hasClass( $classNamespace, $key );
}

/** @param mixed $value */
function setClass ( string $classNamespace, $value, bool $override = false, string $state = RegClass::class ) : bool
{
    return RegInstance( $state ) ->setClass( $classNamespace, $value, $override );
}

function delClass ( string $classNamespace, string $state = RegClass::class ) : bool
{
    return RegInstance( $state ) ->delClass( $classNamespace );
}

function _debugInfo( string $state ) : array
{
    return RegInstance( $state)->_debugInfo();
}