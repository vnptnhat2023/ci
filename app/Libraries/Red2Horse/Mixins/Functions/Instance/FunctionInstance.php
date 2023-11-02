<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Instance;

use function Red2Horse\Mixins\Functions\NS\baseNamespace;
use function Red2Horse\Mixins\Functions\NS\exceptionNamespace;
use function Red2Horse\Mixins\Functions\NS\registryNamespace;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function classInit ( string $name, string $state, bool $getShared ) : object
{
    $state = registryNamespace( $state );
    return $state::selfInstance() ->init( $name, $getShared );
}

// /**
//  * @return object|\Red2Horse\Mixins\Traits\Object\TraitSingleton
//  */
function getInstance ( string $classNamespace, string $state = 'RegistryClass', bool $getShared = true, $params = null ) : object
{
    return classInit( $classNamespace, $state, $getShared ) ->getInstance( $params );
}

/**
 * @param bool $getShared For adapter only
 * @return object|\Red2Horse\Mixins\Traits\Object\TraitSingleton
 */
function getComponents ( string $name, bool $getShared = true, bool $getAdapter = false ) : object
{
    return classInit( $name, 'RegistryClass', $getShared ) ->getComponents( $getAdapter );
}

/**
 * @return object|\Red2Horse\Mixins\Traits\Object\TraitSingleton
 */
function getBaseInstance ( string $name, bool $getShared = true, string $prefix = '', string $suffix = '' ) : object
{
    return classInit( baseNamespace( $name, $prefix, $suffix ), 'RegistryClass', $getShared ) ->getInstance();
}

function ExceptionInstance ( string $name, bool $getShared = true, string $prefix = '', string $suffix = '' ) : object
{
    return classInit( exceptionNamespace( $name, $prefix, $suffix ), 'RegistryClass', $getShared ) ->getInstance();
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
