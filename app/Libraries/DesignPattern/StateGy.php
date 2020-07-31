<?php namespace App\Libraries\DesignPattern;

class StateGy {

	public function run(t1 $t1Interface)
	{
    $stateGy = new StateGyRunner($t1Interface);
		return $stateGy->run();
	}
}

class StateGyRunner {
  private $Strategy;

	public function __construct(t1 $t1Interface)
	{
		$this->Strategy = $t1Interface;
  }

	public function run()
	{
		return $this->Strategy->requireMethod();
	}
}

interface t1 {
	function requireMethod();
}

class t1class implements t1 {
	function requireMethod()
	{
		return 'From One class';
	}
}

class t2class implements t1 {
	function requireMethod()
	{
		return 'From Two class';
	}
}