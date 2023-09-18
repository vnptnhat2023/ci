<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Database;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserAdapter implements UserAdapterInterface
{
	public function getUserWithGroup ( string $select, array $where ) : array
	{
		return ( array ) model( UserModelAdapter::class )
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
		return model( UserModelAdapter::class )->update( $where, $data );
	}
}