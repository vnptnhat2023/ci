<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

class UserAdapter implements UserAdapterInterface
{
	protected UserModelAdapter $user;

	public function __construct ( UserModelAdapter $user )
	{
		$this->user = $user;
	}

	public function getUserById ( array $where ) : array
	{
		return (array) $this->user ->where( $where ) ->first();
	}

	public function updateUserById ( int $id, array $data ) : bool
	{
		return $this->user->update( $id, $data );
	}
}