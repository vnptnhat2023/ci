<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Config\ConstantNamespace as CTNS;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access not allowed.' );

function configNamespace( string $name ) : string
{
    return CTNS::LIST_NAMESPACE[ 'CONFIG_NAMESPACE' ] . ucfirst( $name );
}

function functionNamespace( string $name ) : string
{
    return CTNS::LIST_NAMESPACE[ 'FUNCTION_NAMESPACE' ] . ucfirst( $name );
}

function ComponentNamespace( string $name, string $type = 'facade', ?string $diff = null ) : string
{
    if ( ! in_array( $type, [ 'facade', 'adapter' ] ) )
    {
        throw new \Error( 'Type not in [ facade, adapter ]' );
    }

    $ucfirstType = ucfirst( $type == 'facade' ? 'facade' : 'adapter' );
    $name = ucfirst( $name );
    $diff = ( null === $diff ) ? null : ucfirst( $diff );

    return sprintf(
        '%s%s\\%s%s',
        CTNS::LIST_NAMESPACE[ strtoupper( $type ) . '_NAMESPACE' ],
        $name,
        $diff ?? $name,
        $ucfirstType
    );
}