<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

use CodeIgniter\Model;

class UserAdapter implements UserAdapterInterface
{
	protected Model $user;

	public function __construct ( Model $user )
	{
		$this->user = $user;
	}

	public function getUserById ( array $where ) : array
	{
		$this->user->where();
		return $this->user->find();
	}

	public function updateUserById ( int $id, array $data ) : bool
	{
		return $this->user->update( $id, $data );
	}
}