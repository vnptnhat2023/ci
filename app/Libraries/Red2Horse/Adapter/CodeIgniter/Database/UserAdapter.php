<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Database;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\getTable;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserAdapter implements UserAdapterInterface
{
	use TraitSingleton;

	/** @return mixed false|string|object */
	public function query ( string $str, array $data, bool $getString = true )
	{
		if ( ! $query = db_connect()->query( $str, $data ) )
		{
			return false;
		}
		
		if( $getString )
		{
			return $query->getQuery();
		}

		return $query;
	}

	public function querySimple ( string $str )
	{
		return db_connect()->simpleQuery( $str );
	}

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
		$tableUser = getTable( 'user' );
		$model = model( UserModelAdapter::class );
		$model->table = $tableUser;

		return $model->update( $where, $data );
	}

	public function updateUserGroup ( $where, array $data ) : bool
	{
		$tableUserGroup = getTable( 'user_group' );
		$model = model( UserGroupModelAdapter::class );
		$model->table = $tableUserGroup;

		return $model->update( $where, $data );
	}
}