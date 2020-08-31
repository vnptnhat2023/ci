<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Exceptions\ModelException;
use CodeIgniter\Model;
use Config\Services;

/**
 * @source https://github.com/joeylevy/CI_throttle/blob/master/library/Throttle
 */

class ThrottleModelAdapter extends Model
{
  private int $attempts = 0;
  private int $type = 1;
  private int $captchaAttempts = 5;
  private int $maxAttempts = 10;
	private int $timeoutAttempts = 1800;

	private BaseConfig $cacheConfig;
	private string $cacheName = '';

  protected $table = 'throttle';
	protected $tempReturnType = 'object';

	protected $dateFormat = 'datetime';
	protected $useTimestamps = true;
	protected $createdField = 'created_at';

	protected $allowedFields = [
		'ip',
		'type',
		'created_at',
		'updated_at'
	];

  public function __construct ()
  {
		$config = config( 'Cache', false );
		$config->storePath .= 'Red2HorseAuth';

		$this->cacheConfig = $config;

		$this->cacheName = str_replace(
			[ ':', '.', ' ', '_' ],
			'-',
			Services::request() ->getIPAddress()
		);
  }

  public function config (
		int $type,
		int $captchaAttempts,
		int $maxAttempts,
		int $timeoutAttempts
	) : self
	{
		$this->type = $type;
		$this->captchaAttempts = $captchaAttempts;
		$this->maxAttempts = $maxAttempts;
		$this->timeoutAttempts = $timeoutAttempts;

		if ( $this->cache() ->isSupported() )
		{
			if ( $cacheData = $this->cache() ->get( $this->cacheName ) ) {
				$this->attempts = $cacheData[ 'login_attempts' ];
			}
		}
		else
		{
			$whereQuery = [
				'ip' => Services::request() ->getIPAddress(),
				'type' => $type
			];

			$row = $this
			->select( "COUNT({$this->primaryKey}) as count", false )
			->where( $whereQuery )
			->first();

			if ( null === $row ) {
				throw new ModelException('The number of row cannot be empty' );
			}

			$this->attempts = $row->count;
		}

		return $this;
	}

	public function getAttempts()
	{
		return $this->attempts;
	}

  public function showCaptcha () : bool
  {
		return $this->attempts >= --$this->captchaAttempts;
  }

  public function limited () : bool
  {
		return $this->attempts >= $this->maxAttempts;
	}

  /**
   * Throttle multiple connections attempts to prevent abuse
   * @return int attempts
   */
  public function throttle () : int
  {
		if ( $this->limited() ) return $this->maxAttempts;

		if ( $this->cache() ->isSupported() ) return $this->throttle_cache();

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
			$time = strtotime( '-' . (int) $this->timeoutAttempts . ' minutes' );
			$from = date( 'Y-00-00 00:00:00' );
			$to = date( 'Y-m-d H:i:s', $time );

			// $this ->builder()
			// ->where( "created_at BETWEEN '{$from}' AND '{$to}'")
			// ->where( 'ip', Services::request() ->getIPAddress() )
			// ->delete( [ 'type' => $this->type ], 100 );
			$this
			->where( "created_at BETWEEN '{$from}' AND '{$to}'")
			->where( 'ip', Services::request() ->getIPAddress() )
			->where( [ 'type' => $this->type ] )
			->delete( null , true );
		}
	}

	private function throttle_db () : int
	{
		$data = [
			'ip' => Services::request() ->getIPAddress(),
			'type' => $this->type,
			'created_at' => date( 'Y-m-d H:i:s', time() )
		];

		// $this->builder() ->insert( $data );
		if ( false === $this->insert( $data, false ) ) {
			$err = (array) $this->errors( true );
			log_message( 'error', implode( ',', $err ) );
		}

    return $this->attempts;
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

		$this->attempts = $data[ 'login_attempts' ];
		$this->cache() ->save( $this->cacheName, $data, $this->timeoutAttempts );

		return $this->attempts;
	}

	/**
	 * Override default config
	 * @return CacheInterface
	 */
	private function cache () : CacheInterface
	{
		return Services::cache( $this->cacheConfig, false );
	}
}