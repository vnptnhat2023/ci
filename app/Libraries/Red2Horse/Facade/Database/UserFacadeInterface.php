<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

/**
 * List **fields** need to allowed:
 * 'last_activity',
 * 'last_login',
 * 'password',
 * 'session_id',
 * 'cookie_token'
 */
interface UserFacadeInterface
{
	/**
	 * Fetch a user row
	 */
	public function getUser ( string $select, array $where ) : array;

	public function getUserWithGroup ( string $select, array $where ) : array;

	/**
	 * @param integer|array|string $where
	 * @param array $data
	 * @return bool
	 */
	public function updateUser ( $where, array $data ) : bool;
}