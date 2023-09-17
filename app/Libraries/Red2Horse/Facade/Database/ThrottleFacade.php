<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Database;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\getConfig;

class ThrottleFacade implements ThrottleFacadeInterface
{
	use TraitSingleton;

	protected ThrottleFacadeInterface $throttle;

	public function __construct ( ThrottleFacadeInterface $throttle )
	{
		$this->throttle = $throttle;
		$this->init();
	}

	public function init () : void
	{
		if ( getConfig( 'BaseConfig' )->useThrottle )
        {
            $throttle = array_values( ( array ) getConfig( 'throttle' )->throttle );
            $this->config( ...$throttle );
        }
	}

	public function config ( int $type, int $captchaAttempts, int $maxAttempts, int $timeoutAttempts ) : self
	{
		$this->throttle->config( $type, $captchaAttempts, $maxAttempts, $timeoutAttempts );
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