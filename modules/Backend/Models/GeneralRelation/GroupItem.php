<?php

namespace BAPI\Models\GeneralRelation;

class GroupItem extends \CodeIgniter\Model
{
  protected $table = 'general_relation';
  protected $primaryKey = 'id';
  protected $returnType = 'array';
  protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  public function ruleSearch() : array
  {
		$GgRules = config('\BAPI\Config\GeneralGroup')->getRules();

    $GiRules = config('\BAPI\Config\GeneralItem')->getRules();

    foreach ( [ 'title', 'status' ] as $rule ) {
      $rules[ "Item-{$rule}" ] = $GgRules[ $rule ];
		}

    foreach ( [ 'title', 'status' ] as $rule ) {
      $rules[ "Group-{$rule}" ] = $GiRules[ $rule ];
    }

    return $rules;
  }

  public function ruleIndex() : array
  {
		$rules['name'] = config('\BAPI\Config\GeneralRelation')->getRules('name');

		$rules['name_id'] = \Config\Validation::ruleInt(
			'Name-Id', 'if_exist', null, true
		);

    $rules['ggid'] = \Config\Validation::ruleInt(
			'General-Group-Id', 'required'
		);

    return $rules;
  }
}