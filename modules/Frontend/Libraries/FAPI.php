<?php namespace FAPI\Libraries;

use CodeIgniter\Events\Events;

class FAPI {

	private $data = [];

	public function __construct($params = null)
	{
		$this->data[__METHOD__] = 'Assign from construct';

		$this->run($params);
	}

	public function run(FLIB $params = null)
	{
		$this->data[__METHOD__] = 'Assign from RUN';
		$this->data['params'] = $params;
		var_dump($this->data);
		// return $this->data;
	}
}