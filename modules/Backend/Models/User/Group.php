<?php

namespace BAPI\Models\User;

class Group extends \CodeIgniter\Model
{
  protected $table = 'user_group';
  protected $returnType = 'array';
	protected $dateFormat = 'date';

  protected $useSoftDeletes = true;

  protected $beforeInsert = ['__beforeInsert'];
  protected $beforeUpdate = ['__beforeUpdate'];

  public function ruleCreate()
  {
    $rules['name'] = \Config\Validation::modifier(
			config('\BAPI\Config\User')->getRules('user_group_name'),

      null, null, "is_unique[{$this->table}.name]"
    );

		$this->allowedFields = ['name', 'permission', 'deleted_at'];

    return $rules;
  }

  public function rulePatch(array $data)
  {
		$t = $this->table;

		$k = $this->primaryKey;

    $i = $data['id'];

    $rules['name'] = \Config\Validation::modifier(
			config('\BAPI\Config\User')->getRules('user_group_name'),

			null, null, "if_exist|is_unique[{$t}.name,{$k},{$i}]"
		);

    $rules['permission'] = [
      'label' => 'Permission', 'rules' => 'if_exist|inPermission'
    ];

		$this->allowedFields = [ 'name', 'permission' ];

    return $rules;
  }

  public function rulePatchUndelete() : array
  {
		$this->allowedFields = [ $this->deletedField ];

    return [ $this->deletedField => \Config\Validation::ruleUndelete() ];
  }

  public function rulePut(array $data)
  {
    $t = $this->table;
    $k = $this->primaryKey;
    $i = $data['id'];

    $rules['name'] = \Config\Validation::modifier(
			config('\BAPI\Config\User')->getRules('user_group_name'),

			null, 'if_exist', "is_unique[{$t}.name,{$k},{$i}]"
		);

    $rules['permission'] = [
			'label' => 'Permission',
			'rules' => 'if_exist|inPermission'
    ];

		$this->allowedFields = [ 'name', 'permission' ];

    return $rules;
  }

  protected function __beforeInsert(array $data) : array
  {
		$data['data']['permission'] = json_encode( [ 'null' ] );

		$data['data']['deleted_at'] = null;

    return $data;
  }

  protected function __beforeUpdate(array $data) : array
  {
    if ( isset( $data[ 'data' ][ 'permission' ] ) ) {
			$jsonPerm = json_encode( $data[ 'data' ][ 'permission' ] );

      $data[ 'data' ][ 'permission' ] = $jsonPerm;
		}

    return $data;
  }

}