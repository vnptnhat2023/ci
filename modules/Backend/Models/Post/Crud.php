<?php

namespace BAPI\Models\Post;

use CodeIgniter\Model;
use Config\Validation;

class Crud extends Model
{

  protected $table = 'post';
	protected $primaryKey = 'id';

  protected $returnType = 'array';
	protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  protected $beforeInsert = [ '__beforeInsert' ];
  protected $beforeUpdate = [ '__beforeUpdate' ];


  /**
   * Search "**elias** table"-"field" with relations separate dash char
   * @example $rules $rules[ 'user-username' ] = $configRules[ 'user_name' ]
   * @return array rules
   */
  public function ruleSearch () : array
  {
		$configRules = config( '\BAPI\Config\Post' ) ->getRules();

		$postRules = [
			'id',
			'title',
			'slug',
			'status',
			'name',
			'name_id',
			'user_id',
			'media_relation_id',
			'typeof',
		];

    foreach ( $postRules as $rule ) {
      $rules[ $rule ] = $configRules[ $rule ];
		}

		# --- Relation separate with dash char user_group_name
		$rules[ 'user-username' ] = config( '\BAPI\Config\User' )
		->getRules( 'user_name' );

		$rules[ 'user_group-name' ] = config( '\BAPI\Config\User' )
		->getRules( 'user_group_name' );

		# --- Todo: let add more ...

    return $rules;
  }

  public function ruleCreate () : array
  {
		$rules = config( '\BAPI\Config\Post' ) ->getRuleExcept( [ 'id' ] );

		$rules[ 'slug' ] = Validation::modifier(
			$rules[ 'slug' ], null, null, "is_unique[{$this->table}.slug]"
		);

		$this->allowedFields = array_keys( $rules );

    return $rules;
  }

  public function rulePatch () : array
  {
    $rules[ 'group_id' ] = Validation::ruleInt(
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

  protected function __beforeInsert ( array $data ) :array
  {
		print_r( $data ); die;

    return $data;
  }

  protected function __beforeUpdate ( array $data ) :array
  {
    print_r( $data ); die;

    return $data;
  }
}