<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\CodeIgniter\Database;

use CodeIgniter\Model;

class UserModelAdapter extends Model
{
	public $table = 'user';
	protected $primaryKey = 'id';
	protected $returnType = 'array';

	protected $allowedFields = [
		'last_activity',
		'last_login',
		'password',
		'session_id',
		'selector',
		'token'
	];
}