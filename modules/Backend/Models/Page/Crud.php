<?php

namespace BAPI\Models\Page;

use CodeIgniter\Model;
use Config\Validation;

class Crud extends Model
{
  protected $table = 'page';
	protected $primaryKey = 'id';

  protected $returnType = 'array';
  protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  public function ruleCreate() : array
  {
    $rules['title'] = Validation::modifier(
			config('\BAPI\Config\Page')->getRules('title'),
			null,
			null,
			"is_unique[{$this->table}.title]"
		);

    $rules['slug'] = Validation::modifier(
			config('\BAPI\Config\Page')->getRules('slug'),
			null,
			null,
			"is_unique[{$this->table}.slug]"
		);

		$rules['parent_id'] = Validation::ruleInt( 'Parent Id', null, null, true );

		$rules['content'] = config('\BAPI\Config\Page')->getRules('content');

		$rules['status'] = config('\BAPI\Config\Page')->getRules('status');

		$this->allowedFields = [
			'title',
			'slug',
			'parent_id',
			'content',
			'status'
		];

    return $rules;
  }

  public function rulePatch() : array
  {
    # --- Maybe need advanced_position, parent_id and status rule
    # --- For multiple Patch
		$rules['status'] = config('\BAPI\Config\Page')->getRules('status');

    $rules['parent_id'] = Validation::ruleInt(
      'Parent Id', null, null, true
    );

		$this->allowedFields = [ 'status', 'parent_id' ];

    return $rules;
  }

  public function rulePut(array $data) : array
  {
		$Id = $data['id'];

		$Tb = $this->table;

		$Pk = $this->primaryKey;

    $Cr = config('\BAPI\Config\Page')->getRules();

    $rules = [
      'title' => Validation::modifier(
				$Cr['title'], null, null, "is_unique[{$Tb}.title,{$Pk},{$Id}]"
			),

      'slug' => Validation::modifier(
				$Cr['title'], null, null, "is_unique[{$Tb}.slug,{$Pk},{$Id}]"
			),

			'content' => $Cr['content'],

			'parent_id' => Validation::ruleInt(
				'Parent Id', 'if_exist', null, true
			),

			'advanced_content' => $Cr['advanced_content'],

      'advanced_position' => Validation::modifier(
				$Cr['advanced_position'], null, 'if_exist'
			),

			'status' => Validation::modifier( $Cr['status'], null, 'if_exist' ),

			'icon' => $Cr['icon'],

      'sort' => Validation::ruleInt('Sort', 'if_exist', null, true)
    ];

    $this->allowedFields = [
			'title',
			'slug',
			'content',
			'parent_id',
			'advanced_content',
			'advanced_position',
			'status',
			'icon',
			'sort'
		];

    return $rules;
  }

  public function rulePatchUndelete() : array
  {
		$rules[ $this->deletedField ] = Validation::ruleUndelete();

		$this->allowedFields = [ $this->deletedField ];

    return $rules;
  }

}