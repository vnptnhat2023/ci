<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Common;

use Red2Horse\Facade\Common\CommonFacade;

use function Red2Horse\Mixins\Functions\Instance\getComponents;

function getCommonInstance () : CommonFacade
{
    return getComponents( 'common' );
}

function lang( string $line, array $args = [] ) : string
{
    return getCommonInstance()->lang( $line, $args );
}

function assocArray ( array $data ) : bool
{
    return getCommonInstance()->isAssocArray( $data );
}

function nonAssocArray ( array $data ) : bool
{
    return getCommonInstance()->nonAssocArray( $data );
}

function arrayInArray ( array $array1, array $array2 ) : bool
{
    return getCommonInstance()->arrayInArray( $array1, $array2 );
}