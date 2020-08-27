<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;

class ThrottleAdapter implements ThrottleAdapterInterface
{
	protected ThrottleModelAdapter $throttle;

	public function __construct ( ThrottleModelAdapter $throttle )
	{
		$this->throttle = $throttle;
	}

	public function config ( int $type, int $limit_one, int $limit, int $timeout ) : self
	{
		$this->throttle->config( ...func_get_args() );

		return $this;
	}

	public function getAttempts () : int
	{
		return $this->throttle->getAttempts();
	}

	public function showCaptcha () : bool
	{
		return $this->throttle->showCaptcha();
	}

	/**
	 * @return int|false
	 */
	public function limited ()
	{
		$this->throttle->limited();
	}

	public function throttle () : int
	{
		return $this->throttle();
	}

	public function cleanup () : void
	{
		$this->throttle->throttle_cleanup();
	}
}