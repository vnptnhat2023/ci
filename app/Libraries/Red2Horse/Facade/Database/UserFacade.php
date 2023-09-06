<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Database;

use Red2Horse\Mixins\Traits\TraitSingleton;

class UserFacade implements UserFacadeInterface
{
	use TraitSingleton;

	protected UserFacadeInterface $user;

	public function __construct( UserFacadeInterface $user )
	{
		$this->user = $user;
	}

	// public function getUser ( string $select, array $where ) : array
	// {
	// 	return $this->user->getUser( $select, $where );
	// }

	public function getUserWithGroup ( string $select, array $where ) : array
	{
		return $this->user->getUserWithGroup( $select, $where );
	}

	/**
	 * @param integer|array|string $where
	 * @param array $data
	 * @return bool
	 */
	public function updateUser ( $where, array $data ) : bool
	{
		return $this->user->updateUser( $where, $data );
	}

	// public function getColumString ( array $columns = [], bool $join = true ) : string
	// {
	// 	$columns = [
	// 		# user
	// 		'user.id',
	// 		'user.username',
	// 		'user.email',
	// 		'user.status',
	// 		'user.last_activity',
	// 		'user.last_login',
	// 		'user.created_at',
	// 		'user.updated_at',
	// 		'user.session_id',
	// 		'user.selector',
	// 		'user.token',
	// 		...$columns
	// 	];

	// 	if ( true === $join ) {
	// 		# user_group
	// 		$columns[] = 'user_group.id as group_id';
	// 		$columns[] = 'user_group.name as group_name';
	// 		$columns[] = 'user_group.permission';
	// 		$columns[] = 'user_group.role';
	// 	}

	// 	return implode( ',', $columns );
	// }
}