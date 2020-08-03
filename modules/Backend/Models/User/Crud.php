<?php

namespace BAPI\Models\User;

use CodeIgniter\Model;
use Config\Services;

class Crud extends Model
{

  protected $table = 'user';
	protected $primaryKey = 'id';

  protected $returnType = 'array';
	protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  protected $beforeInsert = [ '__beforeCreate' ];
  protected $beforeUpdate = [ '__beforeUpdate' ];


  /**
   * Search: table-field
   * @Use user_group-name
   * @return array rules
   */
  public function ruleSearch () : array
  {
		$configRules = config( '\BAPI\Config\User' )->getRules();

    foreach ( [ 'username', 'password', 'email', 'status' ] as $rule ) {
      $rules[ $rule ] = $configRules[ $rule ];
		}

    $rules[ 'user_group-name' ] = $configRules[ 'user_group_name' ];

    return $rules;
  }

  public function ruleCreate () : array
  {
    $this->allowedFields = [ 'username', 'email', 'password', 'status' ];

		$configRules = config( '\BAPI\Config\User' ) ->getRules();

		$usernameRule = $configRules[ 'username' ];

    $emailRule = $configRules[ 'email' ];

		$usernameRule[ 'rules' ] .= "|is_unique[{$this->table}.username]";

    $emailRule[ 'rules' ] .= "|is_unique[{$this->table}.email]";

    $rules = [
      'username' => $usernameRule,
      'email' => $emailRule,
      'password' => $configRules[ 'password' ],
      'status' => $configRules[ 'status' ]
    ];

    return $rules;
  }

  public function rulePatch () : array
  {
    $rules[ 'group_id' ] = \Config\Validation::ruleInt(
			'Id',
			null,
			"is_not_unique[{$this->table}.group_id]"
		);

    $rules[ 'status' ] = config( '\BAPI\Config\User' ) ->getRules( 'status' );

		$this->allowedFields = [ 'group_id', 'status' ];

    return $rules;
  }

  public function rulePut ( array $data ) : array
  {
		$id = $data[ 'id' ];

    $configRules = config( '\BAPI\Config\User' ) ->getRules();

    $rules = [
      'username' => 'username',
      'group_id' => 'group_id',
      'email' => 'email',
      'status' => 'status',
      'password' => 'password',
      'created' => 'created_at',
      'updated' => 'updated_at',
      'fullname' => 'fullname',
      'phone' => 'phone',
      'gender' => 'gender',
      'birthday' => 'birthday'
    ];

		$rulesRequired = [ 'username', 'group_id', 'email', 'status' ];

		$t = $this->table;
		$pk = $this->primaryKey;

    # Sometime many data just need 1 rule ( key != value )
    foreach ($rules as $key => $value) {

      $rules[ $key ] = $configRules[ $value ];

      if ( $key === 'username' )
      {
        $rules[ 'username' ][ 'rules' ] .= "|is_unique[{$t}.username,{$pk},{$id}]";
      }
      else if ( $key === 'email' )
      {
        $rules[ 'email' ][ 'rules' ] .= "|is_unique[{$t}.email,{$pk},{$id}]";
      }

      if ( ! in_array( $key, $rulesRequired, true ) ) {
        $rules[ $key ][ 'rules' ] .= '|if_exist';
      }
    }

    $this->allowedFields = [
			'username',
			'group_id',
			'email',
			'password',
			'status',
			'created_at',
			'updated_at'
		];

    return $rules;
  }

  # --- User::group::__afterDelete using this method
  public function allowedGroupField ()
  {
    $this->allowedFields[] = 'group_id';
  }

  protected function __beforeCreate ( array $data ) :array
  {
		$password = $data[ 'data' ][ 'password' ];

		$data[ 'data' ][ 'password' ] = Services::NknAuth()
		->getHashPass( $password );

		$data[ 'data' ][ 'group_id' ] = config( '\BAPI\Config\User' )
		->getSetting( 'db.option.default_group' );

    return $data;
  }

  protected function __beforeUpdate ( array $data ) : array
  {
    if ( ! empty( $data[ 'data' ][ 'password' ] ) ) {
			$password = $data[ 'data' ][ 'password' ];

			$data[ 'data' ][ 'password' ] = Services::NknAuth()
			->getHashPass( $password );
    }

    return $data;
  }
}