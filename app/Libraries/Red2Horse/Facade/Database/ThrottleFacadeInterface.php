<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Database;

interface ThrottleFacadeInterface
{
	public function config ( int $type, int $limit_one, int $limit, int $timeout ) : self;

	public function getAttempts() : int;

	public function showCaptcha () : bool;

	/**
	 * @return int|false
	 */
	public function limited ();

	public function throttle () : int;

	public function cleanup () : void;
}