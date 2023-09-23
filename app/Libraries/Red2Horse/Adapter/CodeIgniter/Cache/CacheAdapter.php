<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Cache;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CacheAdapter implements CacheAdapterInterface
{
	public object $cacheConfig;

	public function __construct ()
	{
		$this->cacheConfig = config( 'Cache', false );
	}

	public function getCacheAdapterConfig () : object
	{
		return $this->cacheConfig;
	}

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