<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

use CodeIgniter\Model;

class ThrottleModelAdapter extends Model
{
	protected $table = 'throttle';
	protected $primaryKey = 'id';

	protected $returnType = 'array';
}