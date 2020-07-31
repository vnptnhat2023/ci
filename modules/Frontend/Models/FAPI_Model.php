<?php

namespace FAPI\Models;

class FAPI_Model extends \CodeIgniter\Model
{
	protected $table = 'user';

	public function __construct(string $str = '__construct')
	{
		echo $str . '<br>';
		var_dump($this->builder());
		// echo __METHOD__ . '<br>';
	}
}