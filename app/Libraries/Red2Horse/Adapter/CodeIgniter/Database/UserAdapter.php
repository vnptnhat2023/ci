<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

use App\Libraries\Red2Horse\Facade\Auth\Config;

class UserAdapter implements UserAdapterInterface
{
	protected UserModelAdapter $user;
	protected Config $config;

	public function __construct ( UserModelAdapter $user, Config $config )
	{
		$this->user = $user;
		$this->config = $config;
	}

	public function getUser ( array $where ) : array
	{
		$queryColumn = $this->config->getStringColum( [] );

		return (array) $this->user
		->select( $queryColumn )
		->where( $where )
		->first();
	}

	public function getUserWithGroup ( array $where, array $moreColumns = [] ): array
	{
		$queryColumn = $this->config->getStringColum( $moreColumns );

		return (array) $this->user
		->select( $queryColumn )
		->join( 'user_group', 'user_group.id = User.group_id' )
		->orWhere( $where )
		->get(1)
		->first();
	}

	/**
	 * @param integer|array|string $where
	 * @param array $data
	 * @return bool
	 */
	public function updateUser ( $where, array $data ) : bool
	{
		return $this->user->update( $where, $data );
	}
}