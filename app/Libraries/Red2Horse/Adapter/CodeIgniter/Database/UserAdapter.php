<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Database;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Sql\
{
	getTable,
	getUserField,
	getUserGroupField,
	keyValueExports
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserAdapter implements UserAdapterInterface
{
	use TraitSingleton;

	private array $dbConfig;
	private \CodeIgniter\Database\BaseConnection $db;

	private bool $installed = false;

	protected function __construct() 
	{
		if ( is_file( $databasePath = APPPATH . 'Libraries/Red2Horse/Database.php' ) )
		{
			require $databasePath;

			$this->dbConfig = $Red2HorseDatabase;
			$this->db = db_connect( $Red2HorseDatabase );
			$this->installed = true;
		}
		else
		{
			$this->db = db_connect();
		}
	}

	/** @return mixed false|string|object */
	public function query ( string $str, array $data, bool $getString = true )
	{
		if ( ! $query = $this->db->query( $str, $data ) )
		{
			return false;
		}
		
		if( $getString )
		{
			return $query->getQuery();
		}

		return $query;
	}

	public function querySimple ( string $str )
	{
		return $this->db->simpleQuery( $str );
	}

	public function getUserWithGroup ( string $select, array $where ) : array
	{
		$userTBL = getTable( 'user' );
		$userId = getUserField( 'id' );
		$userGroupTBL = getTable( 'user_group' );
		$userGroupId = getUserGroupField( 'id' );

		$model = $this->_getModel();
		$model->table = $userTBL;

		$on = sprintf(
			'%s = %s.%s',
			empty( $userGroupId[ 2 ] ) ? $userGroupId : $userGroupId[ 2 ],
			$userTBL,
			empty( $userId[ 2 ] ) ? $userId : $userId[ 2 ]
		);

		return ( array ) $model
			->select( $select )
			->join( $userGroupTBL, $on )
			->orWhere( $where )
			->first();
	}

	// /**
	//  * @param integer|array|string $where
	//  * @param array $data
	//  * @return bool
	//  */
	// public function updateUser ( $where, array $data ) : bool
	// {
	// 	$model = $this->_getModel();
	// 	$model->table = getTable( 'user' );

	// 	return $model->update( $where, $data );
	// }

	public function updateUser ( $where, array $data ) : bool
	{
		$where = keyValueExports( $where );
		$data = keyValueExports( $data );
		$table = getTable( 'user' );
		$sql = sprintf( 'UPDATE %s' );

		return $this->querySimple( $sql );
	}

	public function updateUserGroup ( $where, array $data ) : bool
	{
		$model = $this->_getModel();
		$model->table = getTable( 'user_group' );

		return $model->update( $where, $data );
	}

	private function _getModel () : object
	{
		if ( $this->installed )
		{
			$db_connect = db_connect( $this->dbConfig );
			$model = new UserModelAdapter( $db_connect );
		}
		else
		{
			$model = model( UserModelAdapter::class );
		}

		return $model;
	}
}