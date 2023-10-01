<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Mixins\Classes\Registry\RegistryClass as Reg;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function initConfig () : void
{
    getConfig( 'BaseConfig' );
    getComponents( 'Throttle' );
}

/**
 * @param ?string $name null: base config
//  * @return object|\Red2Horse\Mixins\Traits\TraitSingleton
 * @throws \Error
 */
function getConfig ( string $name = '', bool $getShared = true ) : object
{
    if ( '' === $name )
    {
        return getInstance( configNamespace( 'BaseConfig' ), Reg::class, $getShared );
    }

    # Class name
    if ( false === strpos( $name, '\\' ) )
    {
        $name = configNamespace( $name );
        return getInstance( $name, Reg::class, $getShared );
    }

    # Namespace class
    return getInstance( $name, Reg::class, $getShared );
}

/**
 * @return \Red2Horse\Mixins\Traits\TraitSingleton
 * @throws \Error
 */
function setConfig ( ?string $name = null, \Closure $callback, bool $getShared = true ) : object
{
    $config = getConfig( $name, $getShared );
    $changed = $callback( $config );

    if ( ! ( $changed instanceof $config ) )
    {
        throw new \Error(
            'Type of [ callback ] return must instance of ' . $name, 406 
        );
    }

    $namespace = get_class( $config );
    /** @var array $oldConfig */
    $oldConfig = getClass( $namespace );

    if ( isset( $oldConfig[ 'properties' ] ) )
    {
        $oldConfig[ 'properties' ] = $changed::_getMPs( [], false, false );
    }
    $oldConfig[ 'methods' ] = $changed::_getMPs();
    $oldConfig[ 'instance' ] = $changed;
    $oldConfig[ 'hasChanged' ] = true;
    
    if ( ! setClass( $namespace, $oldConfig, true ) )
    {
        throw new \Error( 'Cannot update: ' . $namespace, 304 );
    }

    if ( method_exists( $changed, 'reInit' ) )
    {
        $changed->reInit();
    }

    return $changed;
}

function hasConfig ( string $name ) : bool
{
    return hasClass( configNamespace( $name ) );
}