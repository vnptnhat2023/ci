<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Auth;

use Red2Horse\Mixins\Classes\Base\Authorization;

use function Red2Horse\Mixins\Functions\Instance\BaseInstance;
use function Red2Horse\Mixins\Functions\Sql\getUserGroupField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function withSession ( string $sessKey, array $data, string $condition = 'or' ) : bool
{
    /** @var string $sessKey getUserGroupField::[ role, permission] */
    $userGroupField = getUserGroupField( $sessKey );
    return BaseInstance( Authorization::class )
        ->withSession( $userGroupField, $data, $condition );
}