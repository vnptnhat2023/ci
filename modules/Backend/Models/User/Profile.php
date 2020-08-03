<?php

namespace BAPI\Models\User;

use CodeIgniter\Model;
use Config\Services;

class Profile extends Model
{
  protected $table = 'user';
  protected $returnType = 'array';
	protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  protected $beforeUpdate = [ 'beforeUpdate' ];

  public function rulePut () : array
  {
		$currentUser = Services::NknAuth() ->getUserdata( 'id' );

		$configRules = config( '\BAPI\Config\User' ) ->getRules();

    $rules = [ 'email', 'password', 'fullname', 'phone', 'gender', 'birthday' ];

    foreach ( $rules as $rule ) {
      $rules[ $rule ] = $configRules[ $rule ];

      if ( $rule === 'email' )
      {
        $rules[ 'email' ][ 'rules' ] .=
        "|is_unique[{$this->table}.email,{$this->primaryKey},{$currentUser}]";
      }
      else
      {
        $rules[ $rule ][ 'rules' ] .= '|if_exist';
			}

    }

		# --- Validate for other table
		$this->allowedFields = [ 'email', 'password' ];

    return $rules;
  }

  protected function beforeUpdate ( array $data ) : array
  {
    if ( ! empty( $data[ 'data' ][ 'password' ] ) ) {
			$password = $data[ 'data' ][ 'password' ];

      $data['data']['password'] = Services::NknAuth()->getHashPass( $password );
		}

    return $data;
  }

}