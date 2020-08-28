<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

class UserFacade implements UserFacadeInterface
{
	protected UserFacadeInterface $user;

	public function __construct( UserFacadeInterface $user )
	{
		$this->user = $user;
	}

	public function getUser ( array $where ) : array
	{
		return $this->user->getUser( $where );
	}

	public function getUserWithGroup ( array $where, array $moreColumns = [] ) : array
	{
		return $this->getUserWithGroup( $where, $moreColumns = [] );
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