<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

interface UserFacadeInterface
{
	public function getUserById ( int $id, array $data ) : array;

	public function updateUserById ( int $id, array $data ) : bool;
}