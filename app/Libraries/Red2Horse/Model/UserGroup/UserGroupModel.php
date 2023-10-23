<?php

declare( strict_types = 1 );
namespace Red2Horse\Model\UserGroup;

use Red2Horse\Facade\Query\QueryFacadeInterface;
use Red2Horse\Mixins\Classes\Sql\Model;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserGroupModel extends Model
{
    public array $allowedFields = [ 'created_at', 'user_group.id', 'user_group.role' ];
    public array $createdAt = [ 'created_at' => 'Y-m-d H:i:s' ];

    public function __construct ( ?string $table = null, ?QueryFacadeInterface $connection = null )
    {
        parent::table( $table ?: 'user_group', $connection );
    }
}