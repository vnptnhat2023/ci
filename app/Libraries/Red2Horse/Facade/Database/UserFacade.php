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

	public function getUserById ( array $where ) : array
	{
		return $this->user->getUserById( $where );
	}

	public function updateUserById ( int $id, array $data ) : bool
	{
		return $this->user->updateUserById( $id, $data );
	}
}