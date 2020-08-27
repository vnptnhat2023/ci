<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

class ThrottleFacade implements ThrottleFacadeInterface
{
	protected $throttle;

	public function __construct( ThrottleFacadeInterface $throttle )
	{
		$this->throttle = $throttle;
	}

	public function config ( int $type, int $limit_one, int $limit, int $timeout ) : self
	{
		return $this->throttle->config( ...func_get_args() );
	}

	public function getAttempts() : int
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
		return $this->throttle->throttle();
	}

	public function cleanup () : void
	{
		$this->throttle->cleanup();
	}
}