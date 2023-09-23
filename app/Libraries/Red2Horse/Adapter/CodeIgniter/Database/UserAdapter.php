<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Database;

use function Red2Horse\Mixins\Functions\getConfig;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserAdapter implements UserAdapterInterface
{
	public function getUserWithGroup ( string $select, array $where ) : array
	{
		$model = model( UserModelAdapter::class );
		return ( array ) $model
		->select( $select )
		->join( 'user_group', 'user_group.id = user.group_id' )
		->orWhere( $where )
		->first();
	}

	/**
	 * @param integer|array|string $where
	 * @param array $data
	 * @return bool
	 */
	public function updateUser ( $where, array $data ) : bool
	{
		$config = getConfig( 'Sql' );
		$tableUser = $config->tables[ 'tables' ][ 'user' ];
		$model = model( UserModelAdapter::class );
		$model->table = $tableUser;

		return $model->update( $where, $data );
	}

	public function updateUserGroup ( $where, array $data ) : bool
	{
		$config = getConfig( 'Sql' );
		$tableUserGroup = $config->tables[ 'tables' ][ 'user_group' ];
		$model = model( UserGroupModelAdapter::class );
		$model->table = $tableUserGroup;

		return $model->update( $where, $data );
	}
}