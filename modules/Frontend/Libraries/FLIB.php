<?php namespace FAPI\Libraries;

/**
 * Test for fun nothing do
 */
final class FLIB {

	private $data = [];

	public function __construct($params = null)
	{
		$this->data[__CLASS__] = __METHOD__;
		$this->run($params);
	}

	public function run($params = null)
	{
		$this->data[__CLASS__] = __METHOD__;
		$this->data['params'] = $params;
	}
}