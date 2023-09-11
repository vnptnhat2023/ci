<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Mixins\Classes\Registry\RegistryClass as RegClass;

/**
 * namespace Red2Horse\Mixins\Functions\FunctionClass.php
 * 
 * classInit
 * getInstance
 * getComponents
 * getInstanceMethods
 * callClass
 */

function classInit( string $classNamespace, string $state, bool $getShared ) : object
{
    return $state::selfInstance() ->init( $classNamespace, $getShared );
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
 */
function callClass ( string $classNamespace, string $state = RegClass::class, bool $getShared = true, array $arguments = [] ) : object
{
    return classInit( $classNamespace, $state, $getShared ) ->callClass( $arguments );
}
