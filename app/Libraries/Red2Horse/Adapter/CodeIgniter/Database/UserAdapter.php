<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

use App\Libraries\Red2Horse\Facade\Auth\Config;

class UserAdapter implements UserAdapterInterface
{
	protected UserModelAdapter $user;
	protected Config $config;

	public function __construct ()
	{
		$this->user = model(
			'App\Libraries\Red2Horse\Adapter\CodeIgniter\Database\UserModelAdapter'
		);
		$this->config = new Config();
	}

	public function getUser ( array $where ) : array
	{
		$queryColumn = $this->config->getColumString( [ 'user.cookie_token' ], false );

		return (array) $this->user
		->select( $queryColumn )
		->where( $where )
		->first();
	}

	public function getUserWithGroup ( array $where, array $moreColumns = [] ) : array
	{
		$queryColumn = $this->config->getColumString( $moreColumns );

		return (array) $this->user
		->select( $queryColumn )
		->join( 'user_group', 'user_group.id = user.group_id' )
		->orWhere( $where )
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