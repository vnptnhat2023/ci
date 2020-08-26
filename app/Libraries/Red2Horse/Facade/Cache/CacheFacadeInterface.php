<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Cache;

interface CacheFacadeInterface
{
	public function get ( string $key );

	public function set ( string $key, $value, $timeToLife = 86400 );
}