<?php

declare( strict_types = 1 );
namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

# --------------------------------------------------------------------------

class UserAdapter implements UserAdapterInterface
{
	protected UserModelAdapter $user;

	public function __construct ()
	{
		$this->user = model( UserModelAdapter::class );
	}

	public function getUser ( string $select, array $where ) : array
	{
		return (array) $this->user->select( $select )->where( $where )->first();
	}

	public function getUserWithGroup ( string $select, array $where ) : array
	{
		return (array) $this->user
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
		return $this->user->update( $where, $data );
	}
}