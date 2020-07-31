<?php

namespace BAPI\Models\User;

class Detail extends \CodeIgniter\Model
{
  protected $primaryKey = 'user_id';
  protected $table = 'user_detail';
  protected $returnType = 'array';

  protected $useSoftDeletes = true;
  protected $useTimestamps = false;

  protected $allowedFields = [
		'user_id',
		'fullname',
		'phone',
		'gender',
		'birthday'
	];
}