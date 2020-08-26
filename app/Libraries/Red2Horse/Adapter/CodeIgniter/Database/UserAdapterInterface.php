<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;
use App\Libraries\Red2Horse\Facade\Database\UserFacadeInterface;

interface UserAdapterInterface extends UserFacadeInterface
{
	public function getUserById ( array $where ) : array;

	public function updateUserById ( int $id, array $data ) : bool;
}