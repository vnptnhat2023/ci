<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Facade\Auth\Config;
use Red2Horse\Mixins\
{
    RegistryClass___ as Registry,
    CallClass___ as Call
};

/**
 * @return mixed array|null
 */
function instanceData ( string $className )
{
    if ( null === $classData = Registry::get( $className ) )
    {
        try
        {
            $className::getInstance();
            $classData = Registry::get( $className );
        }
        catch (\Throwable $th)
        {
            throw $th;
        }
    }

    return $classData;
}

/**
 * @return mixed array|object
 */
function getClass ( string $className, string $key = '' )
{
    $classData = instanceData( $className );
    return $classData[ $key ] ?? $classData;
}

/**
 * @return self
 */
function getInstance ( string $className, bool $getShared = true )
{
    if ( $getShared )
    {
        $classData = instanceData( $className );
        return $classData[ 'instance' ];
    }

    return new $className;
}

/**
 * @param string $name name only ( not namespace )
 * @param bool $getShared adapter only
 */
function getComponents ( string $name, bool $getShared = true ) : object
{
    $config = getInstance( Config::class, $getShared );
    $name = ucfirst( $name );
    $facadeName = $config->facade( $name );

    if ( in_array( $name, [ 'User', 'Throttle' ], true ) )
    {
        $adapterName = $config->adapter( 'Database', $name );
        $facadeName = $config->facade( 'Database', $name );
    }
    else
    {
        $adapterName = $config->adapter( $name );
    }

    if ( $getShared && method_exists( $adapterName, 'getInstance' ) )
    {
        $adapterInstance = $adapterName::getInstance();
    }
    else
    {
        return new $facadeName( new $adapterName );
    }

    return $facadeName::getInstance( $adapterInstance );
}

function getInstanceMethods ( string $className, bool $getShared = true ) : array
{
    if ( $getShared )
    {
        $classData = instanceData( $className );
        return $classData[ 'methods' ];
    }

    return get_class_methods( $className );
}

function setClass ( string $className, array $value, bool $override = false ) : bool
{
    return Registry::set( $className, $value, $override );
}

function delClass ( string $className ) : bool
{
    return Registry::delete( $className );
}

/**
 * @param bool $getShared true: callClass___ class; false: anonymous class
 */
function callClass ( string $className, bool $getShared = true, array $arguments = [] ) : object
{
    if ( $getShared )
    {
        $instance = Call::getInstance( $className, $arguments );
    }
    else
    {
        $instance = new class( $className, $arguments )
        {
            use \Red2Horse\Mixins\TraitCall;

            public function __construct( string $className, array $arguments )
            {
                $this->traitCallback[ 'before' ] = $arguments[ 'traitCallback' ][ 'before' ] ?? false;
                $this->traitCallback[ 'after' ] = $arguments[ 'traitCallback' ][ 'after' ] ?? false;
                $this->run( $className );
            }
        };
    }

    return $instance;
}