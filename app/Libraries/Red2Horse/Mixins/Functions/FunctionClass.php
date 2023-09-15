<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Config\ConstantNamespace;
use Red2Horse\Mixins\Classes\Registry\RegistryClass as RegClass;

function classInit( string $classNamespace, string $state, bool $getShared ) : object
{
    return $state::selfInstance() ->init( $classNamespace, $getShared );
}

/**
 * @param ?string $name null: base config
 * @throws \Error
 */
function getConfig ( ?string $name = null ) : object
{
    $configNamespace = ConstantNamespace::LIST_NAMESPACE['CONFIG_NAMESPACE'];

    if ( null === $name )
    {
        return getInstance( $configNamespace . 'BaseConfig' );
    }

    if ( false === strpos( $name, '\\' ) )
    {
        $name = $configNamespace . ucfirst( $name );
        return getInstance( $name );
    }

    # Namespace class
    return getInstance( $name );
}

/**
 * @return mixed object|false
 * @throws \Error
 */
function setConfig ( ?string $classNamespace = null, \Closure $callback )
{
    $configNamespace = ConstantNamespace::LIST_NAMESPACE['CONFIG_NAMESPACE'];

    $config = ( null === $classNamespace )
        ? getInstance( $configNamespace . 'BaseConfig' )
        : getInstance( $classNamespace );

    $changed = $callback( $config );

    if ( ! ( $changed instanceof $config ) )
    {
        throw new \Error( 'Type of [ callback ] return must instance of Config.', 403 );
    }

    $changedInstance = getClass( $classNamespace );

    $changedInstance[ 'instance' ] = $changed;
    $changedInstance[ 'methods' ] = $changed::_getMPs();

    if ( ! empty( $changedInstance[ 'properties' ] ) )
    {
        $newProperties = $changed::_getMPs( [], false, false );
        $changedInstance[ 'properties' ] = $newProperties;
    }

    if ( setClass( $classNamespace, $changedInstance, true ) )
    {
        return $changed;
    }

    return false;
}

function getInstance ( string $classNamespace, string $state = RegClass::class, bool $getShared = true )
{
    return classInit( $classNamespace, $state, $getShared ) ->getInstance();
}

/**
 * @param string $class Class name only ( not contain namespace )
 * @param bool $getShared For adapter only
 */
function getComponents ( string $class, string $state = RegClass::class, bool $getShared = true ) : object
{
    return classInit( $class, $state, $getShared ) ->getComponents();
}

function getInstanceMethods ( string $classNamespace, string $state = RegClass::class, bool $getShared = true ) : array
{
    return classInit( $classNamespace, $state, $getShared ) ->getInstanceMethods();
}

/**
 * @param bool $getShared true: callClass___ class; false: anonymous class
 * @return mixed
 */
function callClass ( string $classNamespace, string $state = RegClass::class, bool $getShared = true, array $arguments = [] )
{
    return classInit( $classNamespace, $state, $getShared ) ->callClass( $arguments );
}
