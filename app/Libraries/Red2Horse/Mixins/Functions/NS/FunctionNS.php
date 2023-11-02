<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\NS;

use Red2Horse\Config\ConstantNamespace;
use Red2Horse\Exception\ErrorParameterException;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function modelNamespace ( string $name = '', string $prefix = '', string $suffix = '' ) : string
{
    return formatFunctionNamespace( $name, 'MODEL_NAMESPACE', $prefix, $suffix );
}

function configNamespace ( string $name = '', string $prefix = '', string $suffix = '' ) : string
{
    return formatFunctionNamespace( $name, 'CONFIG_NAMESPACE', $prefix, $suffix );
}

function baseNamespace ( string $name = '', string $prefix = '', string $suffix = '' ) : string
{
    return formatFunctionNamespace( $name, 'BASE_NAMESPACE', $prefix, $suffix );
}

function exceptionNamespace ( string $name = '', string $prefix = '', string $suffix = '' ) : string
{
    return formatFunctionNamespace( $name, 'EXCEPTION_NAMESPACE', $prefix, $suffix );
}

function functionNamespace ( string $name = '', string $prefix = '', string $suffix = '' ) : string
{
    return formatFunctionNamespace( $name, 'FUNCTION_NAMESPACE', $prefix, $suffix );
}

function registryNamespace ( string $name = '', string $prefix = '', string $suffix = '' ) : string
{
    return formatFunctionNamespace( $name, 'REGISTRY_NAMESPACE', $prefix, $suffix );
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
        '' === $prefix || $prefix = sprintf( '%s\\', ucfirst( trim( $prefix, ' \\' ) ) );
        '' === $suffix || $suffix = sprintf( '\\%s', ucfirst( trim( $suffix, ' \\' ) ) );
        $name = ucfirst( trim( $name, '' ) );

        $name = sprintf(
            '%s%s%s%s',
            $prefix,
            $configNamespaces[ strtoupper( $configNamespace ) ],
            $name,
            $suffix
        );
    }

    return $name;
}

function ComponentNamespace ( string $name, string $type = 'facade', ?string $diff = null ) : string
{
    if ( ! in_array( $type, [ 'facade', 'adapter' ] ) )
    {
        throw new ErrorParameterException( 'Type not in: "facade, adapter"' );
    }

    $ucfirstType = ucfirst( $type == 'facade' ? 'facade' : 'adapter' );
    $name        = ucfirst( trim( $name ) );
    $diff        = ( null === $diff ) ? null : ucfirst( trim( $diff ) );

    return sprintf(
        '%s%s\\%s%s',
        ConstantNamespace::LIST_NAMESPACE[ strtoupper( $type ) . '_NAMESPACE' ],
        $name,
        $diff ?? $name,
        $ucfirstType
    );
}