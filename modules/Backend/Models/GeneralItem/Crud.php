<?php

namespace BAPI\Models\GeneralItem;

use CodeIgniter\Model;

class Crud extends Model
{

  protected $table = 'general_item';
	protected $primaryKey = 'id';

  protected $returnType = 'array';
  protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  protected $beforeUpdate = [];
  protected $beforeInsert = [];


  public function ruleCreate () : array
	{# 'name', 'name_id': Should i use it?

    $this->allowedFields = [ 'title', 'slug', 'status' ];

    $Cr = config( '\BAPI\Config\GeneralItem' ) ->getRules();

    $rules[ 'title' ] = \Config\Validation::modifier(
			$Cr[ 'title' ],
			null,
			null,
			"is_unique[{$this->table}.title]"
		);

    $rules[ 'slug' ] = \Config\Validation::modifier(
			$Cr[ 'slug' ],
			null,
			null,
			"is_unique[{$this->table}.slug]"
		);

    // $rules[ 'ggid' ] = \Config\Validation::ruleInt( 'General Group Id', 'required' );
    $rules[ 'status' ] = $Cr[ 'status' ];

    return $rules;
  }

  public function rulePut ( array $data ) : array
  {
		$Cr = config( '\BAPI\Config\GeneralItem' ) ->getRules();

    $Id = $data[ 'id' ];

		$titleRuleStr = "is_unique[{$this->table}.title,{$this->primaryKey},{$Id}]";

    $rules[ 'title' ] = \Config\Validation::modifier(
			$Cr[ 'title' ],
			null,
			null,
			$titleRuleStr
		);

		$slugRuleStr = "is_unique[{$this->table}.slug,{$this->primaryKey},{$Id}]";

    $rules[ 'slug' ] = \Config\Validation::modifier(
			$Cr[ 'slug' ],
			null,
			null,
			$slugRuleStr
		);

    $rules[ 'status' ] = \Config\Validation::modifier( $Cr[ 'status' ], null, 'if_exist' );

		$this->allowedFields = [ 'title', 'slug', 'status' ];

    return $rules;
  }

  public function rulePatch () : array
  {
		$this->allowedFields = [ 'status' ];

    return [ 'status' => config( '\BAPI\Config\GeneralItem' ) ->getRules( 'status' ) ];
  }

  public function rulePatchUndelete () : array
  {
		$this->allowedFields = [ $this->deletedField ];

    return [
			$this->deletedField => \Config\Validation::ruleUndelete()
		];
  }
}