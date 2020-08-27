<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Database;
use App\Libraries\Red2Horse\Facade\Database\ThrottleFacadeInterface;

interface ThrottleAdapterInterface extends ThrottleFacadeInterface
{
	public function config (
		int $type,
		int $captchaAttempts,
		int $maxAttempts,
		int $timeoutAttempts
	) : self;

	public function getAttempts() : int;

	public function showCaptcha () : bool;

	/**
	 * @return int|false
	 */
	public function limited () : bool;

	public function throttle () : int;

	public function cleanup () : void;
}