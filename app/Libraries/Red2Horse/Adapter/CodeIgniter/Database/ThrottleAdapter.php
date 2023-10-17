<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Database;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ThrottleAdapter implements ThrottleAdapterInterface
{
	use TraitSingleton;

	private array $dbConfig;
	private \CodeIgniter\Database\BaseConnection $db;

	private \CodeIgniter\Model $throttleModel;

	public function __construct ()
	{
		if ( is_file( $databasePath = APPPATH . 'Libraries/Red2Horse/Database.php' ) )
		{
			require $databasePath;

			$this->dbConfig = $Red2HorseDatabase;
			$this->db = db_connect( $Red2HorseDatabase );

			$this->throttleModel = new ThrottleModelAdapter( $this->db );
		}
		else
		{
			$this->throttleModel = model( ThrottleModelAdapter::class );
		}
	}
	
	public function config ( int $type, int $limit_one, int $limit, int $timeout ) : self
	{
		$this->throttleModel->config( $type, $limit_one, $limit, $timeout );
		return $this;
	}

	public function getAttempts () : int
	{
		return $this->throttleModel->getAttempts();
	}

	public function showCaptcha () : bool
	{
		return $this->throttleModel->showCaptcha();
	}

	public function limited (): bool
	{
		return $this->throttleModel->limited();
	}

	public function throttle () : int
	{
		return $this->throttleModel->throttle();
	}

	public function cleanup () : void
	{
		$this->throttleModel->throttle_cleanup();
	}
}