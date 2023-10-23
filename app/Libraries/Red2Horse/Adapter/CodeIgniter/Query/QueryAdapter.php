<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\Codeigniter\Query;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class QueryAdapter implements QueryAdapterInterface
{
	use TraitSingleton;

	private array $dbConfig;
	private \CodeIgniter\Database\BaseConnection $db;

	private bool $installed = false;

	protected function __construct() 
	{
		if ( is_file( $databasePath = \Red2Horse\R2H_BASE_PATH .'/Database.php' ) )
		{
			require $databasePath;

			$this->dbConfig = $Red2HorseDatabase;
			$this->db = db_connect( $Red2HorseDatabase );
			$this->installed = true;
		}
		// else
		// {
		// 	$this->db = db_connect();
		// }
	}

	public function query ( string $sql, bool $getString = false  )
	{
		if ( isset( $this->db ) )
		{
			$query = $this->db->query( $sql );
		}
		else
		{
			$query = db_connect()->query( $sql );
		}

		return $getString ? $sql : $query;
	}

	public function resultArray ( string $sql ) : array
	{
		if ( isset( $this->db ) )
		{
			$query = $this->db->query( $sql );
		}
		else
		{
			$query = db_connect()->query( $sql );
		}

		return $query->getResultArray();
	}
	
}