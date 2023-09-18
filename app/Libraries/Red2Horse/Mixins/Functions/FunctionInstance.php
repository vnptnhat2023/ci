<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function classInit( string $classNamespace, string $state, bool $getShared ) : object
{
    $state = registryNamespace( $state );
    return $state::selfInstance() ->init( $classNamespace, $getShared );
}

function getInstance ( string $classNamespace, string $state = 'RegistryClass', bool $getShared = true )
{
    return classInit( $classNamespace, $state, $getShared ) ->getInstance();
}

/**
 * @param string $class Class name only ( not contain namespace )
 * @param bool $getShared For adapter only
 */
function getComponents ( string $class, string $state = 'RegistryClass', bool $getShared = true ) : object
{
    return classInit( $class, $state, $getShared ) ->getComponents();
}

function getInstanceMethods ( string $classNamespace, string $state = 'RegistryClass', bool $getShared = true ) : array
{
    return classInit( $classNamespace, $state, $getShared ) ->getInstanceMethods();
}

/**
 * @param bool $getShared true: callClass___ class; false: anonymous class
 * @return mixed
 */
function callClass ( string $classNamespace, string $state = 'RegistryClass', bool $getShared = true, array $arguments = [] )
{
    return classInit( $classNamespace, $state, $getShared ) ->callClass( $arguments );
}
