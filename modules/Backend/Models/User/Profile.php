<?php

namespace BAPI\Models\User;

class Profile extends \CodeIgniter\Model
{
  protected $table = 'user';
  protected $returnType = 'array';
	protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  protected $beforeUpdate = [ 'beforeUpdate' ];

  public function rulePut() : array
  {
		$currentUser = \Config\Services::NknAuth()->get_userdata( 'id' );

		$configRules = config('\BAPI\Config\User')->getRules();

    $rules = [
      'email' => 'email',
      'password' => 'password',
      'fullname' => 'fullname',
      'phone' => 'phone',
      'gender' => 'gender',
      'birthday' => 'birthday'
    ];

    foreach ($rules as $key => $rule) {

      $rules[ $key ] = $configRules[ $rule ];

      if ( $key === 'email' )
      {
        $rules[ 'email' ][ 'rules' ] .=
        "|is_unique[{$this->table}.email,{$this->primaryKey},{$currentUser}]";
      }
      else
      {
        $rules[ $key ][ 'rules' ] .= '|if_exist';
			}

    }

		$this->allowedFields = ['email', 'password'];

    return $rules;
  }

  protected function beforeUpdate(array $data) : array
  {
    if ( ! empty( $data[ 'data' ][ 'password' ] ) ) {
			$password = $data[ 'data' ][ 'password' ];

      $data['data']['password'] = service('NknAuth')->get_password_hash( $password );
		}

    return $data;
  }

}