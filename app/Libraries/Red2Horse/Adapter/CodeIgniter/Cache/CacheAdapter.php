<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Cache;

use CodeIgniter\Cache\CacheInterface;

class CacheAdapter implements CacheAdapterInterface
{
	protected CacheInterface $cache;

	public function __construct( CacheInterface $cache )
	{
		$this->cache = $cache;
	}

	public function get ( string $key )
	{
		return $this->cache->get( $key );
	}

	public function set ( string $key, $value, int $timeToLife = 86400 )
	{
		return $this->set( $key, $value, $timeToLife );
	}

	public function isSupported () : bool
	{
		return $this->cache->isSupported();
	}
}