<?php

namespace App\Models;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Exceptions\ModelException;
use CodeIgniter\Model;
use Config\Services;

/**
 * author Joey Levy
 * url https://github.com/joeylevy/CI_throttle/blob/master/library/Throttle
 */

class Login extends Model
{
  protected $db;

  private int $login_attempts;
  private int $login_type = 1;
  private int $login_limit_one = 5;
  private int $login_limit = 10;
	private int $login_timeout = 1800;

	private BaseConfig $cacheConfig;
	private string $cacheName;

  protected $table = 'throttle';
	protected $tempReturnType = 'object';

	protected $dateFormat = 'datetime';
	protected $useTimestamps = true;
	protected $createdField = 'created_at';

  public function __construct ()
  {
		$config = config( 'Cache', false );
		$config->storePath .= 'NknAuth';
		$cacheName = str_replace(
			[ ':', '.', ' ' ],
			'-',
			Services::request()->getIPAddress()
		);

		$this->cacheName = $cacheName;
		$this->cacheConfig = $config;
    $this->db = db_connect();
  }

  public function config ( int $type, int $limit_one, int $limit, int $timeout ) : self
	{
		$this->login_type = $type;
		$this->login_limit_one = $limit_one;
		$this->login_limit = $limit;
		$this->login_timeout = $timeout;

		$cacheService = Services::cache( $this->cacheConfig, false );

		if ( $cacheData = $cacheService->get( $this->cacheName ) ) {
			$this->login_attempts = $cacheData[ 'login_attempts' ];

			return $this;
		}

		$whereQuery = [
			'ip' => Services::request() ->getIPAddress(),
			'type' => $type
		];

		$row = $this
		->builder()
		->selectCount( $this->primaryKey, 'count' )
		->getWhere( $whereQuery )
		->getRow();

		if ( null === $row ) {
			throw new ModelException('The number of row cannot be empty');
		}

		$this->login_attempts = $row->count;

    return $this;
  }

  public function was_limited_one () : bool
  {
    return $this->login_attempts > $this->login_limit_one ? true : false;
  }

  public function was_limited ()
  {
		if ( $this->login_attempts >= $this->login_limit )
		return $this->login_timeout;

		else
    return false;
	}

  /**
   * Throttle multiple connections attempts to prevent abuse
   * @return int attempts
   */
  public function throttle () : int
  {
		if ( $this->was_limited() ) return $this->was_limited();

		$ipAddress = Services::request() ->getIPAddress();

		if ( Services::cache( $this->cacheConfig, false ) ->isSupported() ) {
			return $this->throttle_cache( $ipAddress );
		}

		return $this->throttle_db( $ipAddress );
  }

  public function throttle_cleanup ()
  {
		$time = strtotime( '-' . (int) $this->login_timeout . ' minutes' );
		$from = date( 'Y-00-00 00:00:00' );
		$to = date( 'Y-m-d H:i:s', $time );

		$this->builder()
		->where( "created_at BETWEEN '{$from}' AND '{$to}'")
		->where( 'ip', Services::request() ->getIPAddress() )
		->delete( [ 'type' => $this->login_type ], 100 );
	}

	private function throttle_db ( string $ipAddress )
	{
		$data = [
			'ip' => $ipAddress,
			'type' => $this->login_type,
			'created_at' => date( 'Y-m-d H:i:s', time() )
		];

    $this->builder() ->insert( $data );

    return $this->login_attempts;
	}

	private function throttle_cache ( string $ipAddress ) : int
	{
		$data = [];

		$oldData = Services::cache( $this->cacheConfig, false )
		->get( $this->cacheName );

		if ( ! empty( $oldData ) )
		{
			$data[ 'login_attempts' ] = ++ $oldData[ 'login_attempts' ];
			unset( $oldData );
		}
		else
		{
			$data[ 'login_attempts' ] = 1;
		}

		$this->login_attempts = $data[ 'login_attempts' ];

		Services::cache( $this->cacheConfig, false )
		->save( $this->cacheName, $data, $this->login_timeout );

		return $this->login_attempts;
	}
}