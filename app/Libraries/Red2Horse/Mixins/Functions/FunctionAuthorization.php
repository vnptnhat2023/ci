<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Facade\Auth\Authorization;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function withSession ( string $sessKey, array $data, string $condition = 'or' ) : bool
{
    /** @var string $sessKey getUserGroupField::role */
    return baseInstance( Authorization::class )
        ->withSession( getUserGroupField( $sessKey ), $data, $condition );
}