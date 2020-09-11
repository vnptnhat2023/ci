<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Cache;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

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
		return $this->set( $key, $value, $timeToLife );
	}

	public function isSupported () : bool
	{
		return $this->cache->isSupported();
	}
}