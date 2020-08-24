<?php

namespace App\Models;

use CodeIgniter\Cache\CacheInterface;
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

  private int $login_attempts = 0;
  private int $login_type = 1;
  private int $login_limit_one = 5;
  private int $login_limit = 10;
	private int $login_timeout = 1800;

	private BaseConfig $cacheConfig;
	private string $cacheName = '';

  protected $table = 'throttle';
	protected $tempReturnType = 'object';

	protected $dateFormat = 'datetime';
	protected $useTimestamps = true;
	protected $createdField = 'created_at';

  public function __construct ()
  {
		$config = config( 'Cache', false );
		$config->storePath .= 'Red2HorseAuth';

		$this->cacheConfig = $config;
		$this->db = db_connect();

		$this->cacheName = str_replace(
			[ ':', '.', ' ', '_' ],
			'-',
			Services::request() ->getIPAddress()
		);
  }

  public function config (
		int $type,
		int $limit_one,
		int $limit,
		int $timeout
	) : self
	{
		$this->login_type = $type;
		$this->login_limit_one = $limit_one;
		$this->login_limit = $limit;
		$this->login_timeout = $timeout;

		if ( $this->cache() ->isSupported() )
		{
			if ( $cacheData = $this->cache() ->get( $this->cacheName ) )
			$this->login_attempts = $cacheData[ 'login_attempts' ];
		}
		else
		{
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
				throw new ModelException('The number of row cannot be empty' );
			}

			$this->login_attempts = $row->count;
		}

		return $this;
	}

	public function getAttempts() : int
	{
		return $this->login_attempts;
	}

  public function showCaptcha () : bool
  {
		return $this->login_attempts >= --$this->login_limit_one;
  }

  public function limited ()
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
		if ( $this->limited() )
		return $this->limited();

		if ( $this->cache() ->isSupported() )
		return $this->throttle_cache();

		return $this->throttle_db();
  }

  public function throttle_cleanup () : void
  {
		if ( $this->cache() ->isSupported() )
		{
			$this->cache() ->delete( $this->cacheName );
		}
		else
		{
			$time = strtotime( '-' . (int) $this->login_timeout . ' minutes' );
			$from = date( 'Y-00-00 00:00:00' );
			$to = date( 'Y-m-d H:i:s', $time );

			$this
			->builder()
			->where( "created_at BETWEEN '{$from}' AND '{$to}'")
			->where( 'ip', Services::request() ->getIPAddress() )
			->delete( [ 'type' => $this->login_type ], 100 );
		}
	}

	private function throttle_db () : int
	{
		$data = [
			'ip' => Services::request() ->getIPAddress(),
			'type' => $this->login_type,
			'created_at' => date( 'Y-m-d H:i:s', time() )
		];

    $this->builder() ->insert( $data );

    return $this->login_attempts;
	}

	private function throttle_cache () : int
	{
		$data = [];
		$oldData = $this->cache() ->get( $this->cacheName );

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
		$this->cache() ->save( $this->cacheName, $data, $this->login_timeout );

		return $this->login_attempts;
	}

	/**
	 * Instance with custom config
	 * @return CacheInterface
	 */
	private function cache () : CacheInterface
	{
		return Services::cache( $this->cacheConfig, false );
	}
}