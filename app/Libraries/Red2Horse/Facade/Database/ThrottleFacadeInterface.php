<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Database;

/**
 * List **fields** need to allowed: 'ip', 'type', 'created_at', 'updated_at'
 */
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