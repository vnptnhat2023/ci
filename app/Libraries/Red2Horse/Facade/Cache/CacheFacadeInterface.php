<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Cache;

interface CacheFacadeInterface
{
	public function getCacheAdapterConfig () : object;

	/** @return mixed */
	public function get ( string $key );

	public function delete ( string $key ) : bool;

	public function set ( string $key, $value, int $timeToLife = 86400 ) : bool;

	public function isSupported () : bool;
}