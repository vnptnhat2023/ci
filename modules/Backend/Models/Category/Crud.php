<?php

namespace BAPI\Models\Category;

use CodeIgniter\Model;

class Crud extends Model
{
  protected $table = 'category';
	protected $primaryKey = 'id';

  protected $returnType = 'array';
  protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  public function ruleCreate() : array
  {
    $this->allowedFields = [
			'name',
			'name_id',
			'title',
			'slug',
			'icon',
			'keyword',
			'parent_id',
			'status',
			'sort'
		];

    $Cr = config('\BAPI\Config\Category')->getRules();

    $rules['title'] = \Config\Validation::modifier(
			$Cr['title'], null, null, "is_unique[{$this->table}.title]"
		);

    $rules['slug'] = \Config\Validation::modifier(
			$Cr['slug'], null, null, "is_unique[{$this->table}.slug]"
		);

		$rules['name'] = $Cr['name'];

		$rules['name_id'] = $Cr['name_id'];

		$rules['icon'] = $Cr['icon'];

    $rules['keyword'] = $Cr['keyword'];

    $rules['parent_id'] = \Config\Validation::ruleInt(
			'Parent Id', 'required', null, true
		);

		$rules['status'] = $Cr['status'];

    $rules['sort'] = \Config\Validation::ruleInt(
			'Sort', 'required', null, true
		);

    return $rules;
  }

  public function rulePut(array $data) : array
  {
		$Cr = config('\BAPI\Config\Category')->getRules();

		$Id = $data['id'];

		$Tb = $this->table;

		$Pk = $this->primaryKey;

    $ruleModifier = function(...$args) {
      return \Config\Validation::modifier(...$args);
		};

    $ruleInt = function(...$args) {
      return \Config\Validation::ruleInt(...$args);
    };

    $rules['title'] = $ruleModifier(
      $Cr['title'], null, null, "is_unique[{$Tb}.title,{$Pk},{$Id}]"
		);

    $rules['slug'] = $ruleModifier(
      $Cr['slug'], null, null, "is_unique[{$Tb}.slug,{$Pk},{$Id}]"
    );

		$rules['name'] =  $Cr['name'];

		$rules['name_id'] =  $ruleInt( 'Name Id', 'if_exist', null, true );

		$rules['icon'] = $Cr['icon'];

		$rules['keyword'] = $Cr['keyword'];

		$rules['parent_id'] =  $ruleInt( 'Parent Id', 'if_exist', null, true );

		$rules['status'] = $ruleModifier( $Cr['status'], null, 'if_exist' );

    $rules['sort'] =  $ruleInt( 'Sort', 'if_exist', null, true );

    $this->allowedFields = [
			'name',
			'name_id',
			'title',
			'slug',
			'icon',
			'keyword',
			'parent_id',
			'status',
			'sort'
		];

    return $rules;
  }

  public function rulePatch() : array
  {
		$this->allowedFields = [ 'status' ];

    return [ 'status' => config('Cat\BAPI\Config\Category')->getRules('status') ];
  }

  public function rulePatchUndelete() : array
  {
		$this->allowedFields = [ $this->deletedField ];

    return [ $this->deletedField => \Config\Validation::ruleUndelete() ];
  }
}