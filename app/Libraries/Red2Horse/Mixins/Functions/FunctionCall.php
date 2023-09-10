<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

// use Red2Horse\Mixins\Classes\Registry\RegistryEventClass as Reg;
use Red2Horse\Mixins\Classes\Registry\RegistryClass as RegClass;

/** @return object $state */
function RegInstance( string $state ) : object
{
    return $state::selfInstance();
}

/**
 * @return mixed array|null
 */
function instanceData ( string $className, string $state = RegClass::class )
{
    return RegInstance( $state ) ->InstanceData( $className );
}

/**
 * @return mixed array|object ( call )
 */
function getClass ( string $className, string $key = '', string $state = RegClass::class )
{
    return RegInstance( $state ) ->getClass( $className, $key );
}

/** @param mixed $value */
function setClass ( string $className, $value, bool $override = false, string $state = RegClass::class ) : bool
{
    return RegInstance( $state ) ->setClass( $className, $value, $override );
}

function delClass ( string $className, string $state = RegClass::class ) : bool
{
    return RegInstance( $state ) ->delClass( $className );
}

function _debugInfo( string $state ) : array
{
    return RegInstance( $state)->_debugInfo();
}