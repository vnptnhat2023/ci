<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Facade\Auth\Authorization;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function withSession ( string $sessKey, array $data, string $condition = 'or' ) : bool
{
    $sqlConfig = getConfig( 'sql' );
    /** @var string $sessKey */
    $tableUserGroup = $sqlConfig->tables[ 'tables' ][ 'user_group' ];
    /** @var string $sessKey */
    $sessKey = $sqlConfig->tables[ $tableUserGroup ][ $sessKey ];

    return getInstance( Authorization::class )->withSession( $sessKey, $data, $condition );
}