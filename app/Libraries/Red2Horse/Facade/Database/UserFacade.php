<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Database;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

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
}