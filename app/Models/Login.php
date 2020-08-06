<?php

namespace App\Models;

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
  private int $login_timeout = 30;

  protected $table = 'throttle';
	protected $tempReturnType = 'object';

	protected $dateFormat = 'datetime';
	protected $useTimestamps = true;
	protected $createdField = 'created_at';

  public function __construct()
  {
    $this->db = db_connect();

    // clean up login attempts older than specified time
    // $this->throttle_cleanup();
  }

  public function config ( int $type = 1, int $limit_one = 5, int $limit = 10, int $timeout = 30 )
  {
		$this->login_type = $type;
		$this->login_limit_one = ( $limit_one - 1 );
		$this->login_limit = ( $limit - 1 );
		$this->login_timeout = $timeout;

		$whereQuery = [
			'ip' => Services::request()->getIPAddress(),
			'type' => $type
		];

		$row = $this
		->builder()
		->selectCount( $this->primaryKey, 'count' )
		->getWhere( $whereQuery )
		->getRow();

		if ( null === $row ) {
			throw new ModelException('Number of rows cannot be empty');
		}

		$this->login_attempts = $row->count;

    return $this;
  }

  public function was_limited_one()
  {
    return $this->login_attempts > $this->login_limit_one ? true : false;
  }

  public function was_limited()
  {
		if ( $this->login_attempts >= $this->login_limit )
		return $this->login_timeout;

		else
    return false;
	}

  /**
   * throttle multiple connections attempts to prevent abuse
   * @return int attempts
   */
  public function throttle()
  {
    if ( $this->was_limited() ) return $this->was_limited();

		$data = [
			'ip' => Services::request() ->getIPAddress(),
			'type' => $this->login_type,
			'created_at' => date( 'Y-m-d H:i:s', time() )
		];

    $this->builder()->insert( $data );

    return $this->login_attempts;
  }

  public function throttle_cleanup()
  {
		$time = strtotime( '-' . (int) $this->login_timeout . ' minutes' );
		$from = date('Y-00-00 00:00:00');
		$to = date( 'Y-m-d H:i:s', $time );

		$this->builder()
		->where( "created_at BETWEEN '{$from}' AND '{$to}'")
		->delete( [ 'type' => $this->login_type ], 100 );
  }
}