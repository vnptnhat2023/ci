<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Database;

use Red2Horse\Adapter\CodeIgniter\Database\UserAdapter;
use Red2Horse\Exception\ErrorDatabaseConnectionException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserFacade implements UserFacadeInterface
{
	use TraitSingleton;

	protected UserAdapter $user;

	public function __construct( UserAdapter $user )
	{
		$this->user = $user;
	}

	/** @return mixed false|string|object */
	public function query ( string $str, array $data, bool $getString = true )
	{
		return $this->user->query( $str, $data, $getString );
	}

	public function querySimple ( string $str )
	{
		return $this->user->querySimple( $str );
	}

	public function getUserWithGroup ( string $select, array $where ) : array
	{
		return $this->user->getUserWithGroup( $select, $where );
	}

	/**
	 * @param integer|array|string $where
	 * @param array $data
	 * @return bool
	 */
	public function updateUser ( $where, array $data ) : bool
	{
		if ( ! $u = $this->user->updateUser( $where, $data ) )
		{
			throw new ErrorDatabaseConnectionException();
		}

		return $u;
	}

	public function updateUserGroup ( $where, array $data ) : bool
	{
		if ( ! $u = $this->user->updateUserGroup( $where, $data ) )
		{
			throw new ErrorDatabaseConnectionException();
		}

		return $u;
	}
}