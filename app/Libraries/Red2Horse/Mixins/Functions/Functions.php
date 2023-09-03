<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Facade\Auth\Config;
use Red2Horse\Mixins\
{
    RegistryClass___ as Registry,
    CallClass___ as Call,
    TraitCall
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
 */
function getComponents ( string $name ) : object
{
    $config = getInstance( Config::class );
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
    
    return $facadeName::getInstance( new $adapterName );
}

function getInstanceMethods ( string $className ) : array
{
    $classData = instanceData( $className );
    return $classData[ 'methods' ];
}

function setClass ( string $className, array $value, bool $override = false ) : void
{
    Registry::set( $className, $value, $override );
}

function delClass ( string $className ) : bool
{
    return Registry::delete( $className );
}

function callClass ( string $className, bool $getShared = true  ) : object
{
    if ( $getShared )
    {
        return getInstance( Call::class )->run( $className );
    }

    $class = new class( $className )
    {
        use TraitCall;

        public function __construct( $className )
        {
            $this->traitCallback[ 'before' ] = true;
            $this->traitCallback[ 'after' ] = true;
            $this->traitCallback[ 'arguments' ] = [ 'argument 2e' ];

            $this->traitBeforePrefix = 'R2h_before_';
            $this->traitBeforePrefix = 'R2h_after_';

            $this->run( $className );
        }
    };

    return $class;
}