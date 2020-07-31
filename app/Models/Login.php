<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\HTTP\Request;
/**
 * author joeylevy
 * url https://github.com/joeylevy/CI_throttle/blob/master/library/Throttle
 */

class Login extends Model
{
  protected $db;

  private $login_attempts;
  private $login_type = 1;
  private $login_limit_one = 5;
  private $login_limit = 10;
  private $login_timeout = 30;

  protected $table = 'throttle';
  // protected $primary_key = 'id';
  // protected $returnType = 'array';
  protected $tempReturnType = 'object';
  protected $useSoftDeletes = false;

  // protected $allowedFields = ['name', 'email'];

  // protected $useTimestamps = true;
  // protected $createdField  = 'created_at';
  // protected $updatedField  = 'updated_at';
  // protected $deletedField  = 'deleted_at';

  protected $validationRules    = [];
  protected $validationMessages = [];
  protected $skipValidation     = false;

  public function __construct()
  {
    $this->db = db_connect();

    // clean up login attempts older than specified time
    // $this->throttle_cleanup();
  }

  /**
   * MY CUSTOM METHOD
   */
  public function config(int $type = 1, int $limit_one = 5, int $limit = 10, int $timeout = 30)
  {
    $this->login_type = $type;
    $this->login_limit_one = $limit_one - 1;
    $this->login_limit = $limit - 1;
    $this->login_timeout = $timeout;

    $request = \Config\Services::request();
    $builder = $this->builder();
    $this->login_attempts = $builder
      ->where([ 'ip' => $request->getIPAddress(), 'type' => $type ])
      ->countAll();

    return $this;
  }

  public function was_limited_one()
  {
    if ($this->login_attempts > $this->login_limit_one)
    {
      return true;// return $this->timeout;
    }
    return false;
  }

  public function was_limited()
  {
    if ($this->login_attempts >= $this->login_limit)
    {
        return $this->login_timeout;
    }
    return false;
  }

  /**
   * end MY CUSTOM METHOD
   */

  /**
   * throttle multiple connections attempts to prevent abuse
   * @param int $type type of throttle to perform.
   *
   */
  public function throttle()
  {
    if ( $this->was_limited() ) return $this->was_limited();

    $request = \Config\Services::request();
    $builder = $this->builder();
    $builder->insert([
        'ip' => $request->getIPAddress(),
        'type' => $this->login_type,
        'created_at' => date('Y/m/d H:i:s', time())
    ]);

    return $this->login_attempts; // return current number of attempted logins
  }

  public function throttle_cleanup()
  {
    $formatted_current_time = date("Y-m-d H:i:s", strtotime('-' . (int) $this->timeout . ' minutes'));
    $modifier = "DELETE FROM `{$this->table}`
      WHERE created_at BETWEEN '1970-00-00 00:00:00'
      AND '{$formatted_current_time}' AND `type` = ':type:'";
    return $this->db->query($modifier, ['type' => $this->login_type]);
  }
}