<?php

declare( strict_types = 1 );
namespace Red2Horse\Model\Throttle;

use Red2Horse\Mixins\Classes\Sql\Model;
use Red2Horse\Facade\Query\QueryFacadeInterface;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ThrottleModel extends Model
{
    protected $allowedFields = [
		'id',
		'attempt',
		'ip',
		'type',
		'type_attempt',
		'type_limit',
        'timeout',
		'created_at',
		'delete_at',
		'updated_at'
	];

    protected array $createdAt = [ 'created_at' => 'Y-m-d H:i:s'];
    protected array $deletedAt = [ 'deleted_at' => 'Y-m-d H:i:s'];
    protected array $updateAt  = [ 'update_at'  => 'Y-m-d H:i:s'];

    public function __construct (  ?string $table = null, ?QueryFacadeInterface $connection = null )
    {
        parent::table( $table ?: 'throttle', $connection );
    }
}