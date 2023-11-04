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
	}

	public function query ( string $sql, bool $getString = false  )
	{
		$query = isset( $this->db ) ? $this->db : db_connect();
		$query->query( $sql );
		return $getString ? $sql : $query;
	}

	public function resultArray ( string $sql ) : array
	{
		$query = isset( $this->db ) ? $this->db : db_connect();
		$data = $query->query( $sql )->getResultArray();
		return $data;
	}
}