<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\NS;

use Red2Horse\Config\ConstantNamespace;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function configNamespace ( string $name = '' ) : string
{
    return formatFunctionNamespace( $name, 'CONFIG_NAMESPACE' );
}

function baseNamespace ( string $name = '' ) : string
{
    return formatFunctionNamespace( $name, 'BASE_NAMESPACE' );
}

function exceptionNamespace ( string $name = '' ) : string
{
    return formatFunctionNamespace( $name, 'EXCEPTION_NAMESPACE' );
}

function functionNamespace ( string $name = '' ) : string
{
    return formatFunctionNamespace( $name, 'FUNCTION_NAMESPACE' );
}

function registryNamespace ( string $name = '', string $prefix = '\\' ) : string
{
    return formatFunctionNamespace( $name, 'REGISTRY_NAMESPACE' );
}

/** @return mixed array|string */
function formatFunctionNamespace ( string $name = '', string $configNamespace, string $prefix = '', string $suffix = '' )
{
    $configNamespaces = ConstantNamespace::LIST_NAMESPACE;

    if ( '' === $name )
    {
        return $configNamespaces;
    }

    if ( false === strpos( $name, '\\' ) )
    {
        $name = sprintf(
            '%s%s%s%s',
            $prefix,
            $configNamespaces[ strtoupper( $configNamespace ) ],
            ucfirst( trim( $name ) ),
            $suffix
        );
    }

    return $name;
}

function ComponentNamespace ( string $name, string $type = 'facade', ?string $diff = null ) : string
{
    if ( ! in_array( $type, [ 'facade', 'adapter' ] ) )
    {
        throw new \Error( 'Type not in [ facade, adapter ]' );
    }

    $ucfirstType = ucfirst( $type == 'facade' ? 'facade' : 'adapter' );
    $name = ucfirst( trim( $name ) );
    $diff = ( null === $diff ) ? null : ucfirst( trim( $diff ) );

    return sprintf(
        '%s%s\\%s%s',
        ConstantNamespace::LIST_NAMESPACE[ strtoupper( $type ) . '_NAMESPACE' ],
        $name,
        $diff ?? $name,
        $ucfirstType
    );
}