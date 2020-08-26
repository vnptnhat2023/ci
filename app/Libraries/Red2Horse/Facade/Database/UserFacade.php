<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

class UserFacade implements UserFacadeInterface
{
	public function getUserById ( array $where ) : array
	{
		return [];
	}

	public function updateUserById ( int $id, array $data ) : bool
	{
		return true;
	}
}