<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

class UserFacade
{
	public function getUserById ( int $id, array $data ) : array
	{
		return [];
	}

	public function updateUserById ( int $id, array $data ) : bool
	{
		return true;
	}
}