<?php namespace BAPI\Models\GeneralGroup;

class Crud extends \CodeIgniter\Model
{
  protected $table = 'general_group';
  protected $primaryKey = 'id';
  protected $returnType = 'array';
  protected $dateFormat = 'date';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;

  protected $beforeUpdate = [];
  protected $beforeInsert = [];

  public function ruleCreate() : array
  {# 'name', 'name_id': Should i use it?
    $this->allowedFields = [ 'title', 'slug', 'status' ];

    $Cr = config('\BAPI\Config\GeneralGroup')->getRules();

    $rules['title'] = \Config\Validation::modifier( $Cr['title'],
      null, null, "is_unique[{$this->table}.title]" );
    $rules['slug'] = \Config\Validation::modifier( $Cr['slug'],
      null, null, "is_unique[{$this->table}.slug]" );
    $rules['status'] = $Cr['status'];

    return $rules;
  }

  public function rulePut(array $data) : array
  {
    $Cr = config('\BAPI\Config\GeneralGroup')->getRules();
    $Id = $data[ 'id' ];

    $rules['title'] = \Config\Validation::modifier(
      $Cr['title'], null, null,
      "is_unique[{$this->table}.title,{$this->primaryKey},{$Id}]"
    );
    $rules['slug'] = \Config\Validation::modifier(
      $Cr['slug'], null, null,
      "is_unique[{$this->table}.slug,{$this->primaryKey},{$Id}]"
    );
    $rules['status'] = \Config\Validation::modifier(
      $Cr['status'], null, 'if_exist'
    );

    $this->allowedFields = [ 'title', 'slug', 'status' ];
    return $rules;
  }

  public function rulePatch() : array
  {
    $this->allowedFields = [ 'status' ];
    return [ 'status' => config('\BAPI\Config\GeneralGroup')->getRules('status') ];
  }

  public function rulePatchUndelete() : array
  {
    $this->allowedFields = [ $this->deletedField ];
    return [ $this->deletedField => \Config\Validation::ruleUndelete() ];
  }
}