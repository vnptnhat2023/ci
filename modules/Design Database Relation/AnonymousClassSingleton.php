<?php
function singletonize(\Closure $func)
{
	$singled = new class($func)
	{
		// Hold the class instance.
		private static $instance = null;
		public function __construct($func = null)
		{
			if (self::$instance === null) {
				self::$instance = $func();
			}
			return self::$instance;
		}
		// The singleton decorates the class returned by the closure
		public function __call($method, $args)
		{
			return call_user_func_array([self::$instance, $method], $args);
		}
		private function __clone(){}
		private function __wakeup(){}
	};
	return $singled;
}