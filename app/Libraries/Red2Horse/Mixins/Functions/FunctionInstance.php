<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Mixins\Classes\Registry\RegistryClass;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @return RegistryClass */
function classInit( string $classNamespace, string $state, bool $getShared ) : object
{
    $state = registryNamespace( $state );
    return $state::selfInstance() ->init( $classNamespace, $getShared );
}

/**
//  * @return \Red2Horse\Mixins\Traits\TraitSingleton
 */
function getInstance ( string $classNamespace, string $state = 'RegistryClass', bool $getShared = true ) : object
{
    return classInit( $classNamespace, $state, $getShared ) ->getInstance();
}

/**
 * @param bool $getShared For adapter only
 * @return \Red2Horse\Mixins\Traits\TraitSingleton
 */
function getComponents ( string $classNamespace, string $state = 'RegistryClass', bool $getShared = true ) : object
{
    return classInit( $classNamespace, $state, $getShared ) ->getComponents();
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
