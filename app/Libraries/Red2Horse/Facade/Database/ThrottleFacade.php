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

	public function config (
		int $type,
		int $captchaAttempts,
		int $maxAttempts,
		int $timeoutAttempts
	) : self
	{
		$this->throttle->config(
			$type,
			$captchaAttempts,
			$maxAttempts,
			$timeoutAttempts
		);

		return $this;
	}

	public function getAttempts() : int
	{
		return $this->throttle->getAttempts();
	}

	public function showCaptcha () : bool
	{
		return $this->throttle->showCaptcha();
	}


	public function limited () : bool
	{
		return $this->throttle->limited();
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