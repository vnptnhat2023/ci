<?php
namespace FAPI\Config;

use \CodeIgniter\Config\BaseConfig;

class Assets extends BaseConfig {
	
	public $CSSPath;
	public $JSPath;

	public function __construct()
	{
		$this->CSSPath = base_url('public/frontend/css/default.css');
		$this->JSPath = base_url('public/frontend/js/default.js');
	}
	
}