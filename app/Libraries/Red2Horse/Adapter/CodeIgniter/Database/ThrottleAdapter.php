<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

class ThrottleAdapter implements ThrottleAdapterInterface
{
	public function config ( int $type, int $limit_one, int $limit, int $timeout ) : self
	{
		return $this;
	}

	public function getAttempts() : int
	{
		return 5;
	}

	public function showCaptcha () : bool
	{
		return true;
	}

	/**
	 * @return int|false
	 */
	public function limited ()
	{

	}

	public function throttle () : int
	{
		return 5;
	}

	public function cleanup () : void
	{
		
	}
}