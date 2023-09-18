<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Cache;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CacheFacade implements CacheFacadeInterface
{
	use TraitSingleton;

	protected CacheFacadeInterface $cache;

	public function __construct ( CacheFacadeInterface $cache )
	{
		$this->cache = $cache;
	}

	public function get ( string $key )
	{
		return $this->cache->get( $key );
	}

	public function set ( string $key, $value, $timeToLife = 86400 )
	{
		return $this->cache->set( $key, $value, $timeToLife );
	}

	public function isSupported () : bool
	{
		return $this->cache->isSupported();
	}
}