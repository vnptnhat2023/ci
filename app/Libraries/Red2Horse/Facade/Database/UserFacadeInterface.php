<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

interface UserFacadeInterface
{
	public function getUserById ( array $where ) : array;

	public function updateUserById ( int $id, array $data ) : bool;
}