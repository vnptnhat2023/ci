<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\CodeIgniter\Cache;

class CacheAdapter implements CacheAdapterInterface
{
	public function get ( string $key )
	{
		return cache()->get( $key );
	}

	public function set ( string $key, $value, int $timeToLife = 86400 )
	{
		return cache()->save( $key, $value, $timeToLife );
	}

	public function isSupported () : bool
	{
		return cache()->isSupported();
	}
}