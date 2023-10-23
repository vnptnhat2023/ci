<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Cache;

use Red2Horse\Adapter\CodeIgniter\Cache\CacheAdapter;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CacheFacade implements CacheFacadeInterface
{
	use TraitSingleton;

	protected CacheAdapter $cache;
	public object $cacheAdapterConfig;

	public function __construct ( CacheAdapter $cache )
	{
		$this->cache = $cache;
		$this->cacheAdapterConfig = $cache->getCacheAdapterConfig();
	}

	public function getCacheAdapterConfig() : object
	{
		return $this->cacheAdapterConfig;
	}

	public function get ( string $key )
	{
		return $this->cache->get( $key );
	}

	public function delete ( string $key ) : bool
	{
		return $this->cache->delete( $key );
	}

	public function set ( string $key, $value, $timeToLife = 86400 ) : bool
	{
		return $this->cache->set( $key, $value, $timeToLife );
	}

	public function isSupported () : bool
	{
		return $this->cache->isSupported();
	}
}