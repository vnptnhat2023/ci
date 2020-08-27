<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

interface ThrottleFacadeInterface
{
	public function config (
		int $type,
		int $captchaAttempts,
		int $maxAttempts,
		int $timeoutAttempts
	) : self;

	public function getAttempts() : int;

	public function showCaptcha () : bool;

	public function limited () : bool;

	public function throttle () : int;

	public function cleanup () : void;
}