<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\CodeIgniter\Database;

use CodeIgniter\Model;

class UserGroupModelAdapter extends Model
{
	public $table = 'user_group';
	protected $primaryKey = 'id';
	protected $returnType = 'array';

	protected $allowedFields = [
		'name',
		'role',
		'permission',
		'deleted_at'
	];
}