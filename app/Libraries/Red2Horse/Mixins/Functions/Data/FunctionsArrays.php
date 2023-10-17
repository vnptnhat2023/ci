<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Data;

use Red2Horse\Mixins\Classes\Data\
{
    DataKeyMap,
    DataAssocKeyMap
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** Non-associative */
function dataKeyInstance ( ) : DataKeyMap
{
    return new DataKeyMap;
}

/**
 * Non-associative
 * @return array|string
 */
function dataKey ( array $data, bool $toString = true, ?\Closure $callable = null )
{
    return dataKeyInstance()->keyMap( $data, $toString, $callable );
}

/**
 * Non-associative
 * @return array|string
 */
function dataKeys ( array $data, bool $toString = true, ?\Closure $callable = null )
{
    return (dataKeyInstance())( $data, $toString, $callable );
}

/** Associative */
function dataKeyAssocInstance ( ) : DataAssocKeyMap
{
    return new DataAssocKeyMap;
}

/**
 * Associative
 * @return array|string
 */
function dataKeyAssoc ( array $data, bool $toString = true, ?callable $callable = null )
{
    return (dataKeyAssocInstance())( $data, $toString, $callable );
}

function castToAssoc ( mixed $data, array $default = [], bool $getAssoc = true ) : array
{
    return dataKeyAssocInstance()::castToAssoc( $data, $default, $getAssoc );
}

function matchKey ( string $str ) : string
{
    return dataKeyAssocInstance()->matchKey( $str );
}

function matchValue ( string $str ) : string
{
    return dataKeyAssocInstance()->matchValue( $str );
}