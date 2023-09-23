<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Cache
{
	use TraitSingleton;

	public string $storePath = 'Red2HorseAuth';

	public function getCacheName ( string $name ) : string
	{
		return str_replace( [ ':', '.', ' ', '_' ], '-', $name );
	}
}