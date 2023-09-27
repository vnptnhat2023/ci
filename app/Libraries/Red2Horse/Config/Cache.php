<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Cache
{
	use TraitSingleton;

	public string $storePath = 'Red2HorseAuth';
	public bool $enabled = true;

	public string $userGroupId = 'get_user_with_group_user_id';
	public int $cacheTTL = 2592000;

	private function __construct () {}

	public function getCacheName ( string $name ) : string
	{
		return str_replace( [ ':', '.', ' ', '_' ], '-', $name );
	}
}