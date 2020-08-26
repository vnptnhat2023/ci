<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Cache;

class CacheFacade implements CacheFacadeInterface
{
	public function get ( string $key )
	{
		return true;
	}

	public function set ( string $key, $value, $timeToLife = 86400 )
	{
		return true;
	}
}