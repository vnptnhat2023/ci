<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Query;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;
use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class QueryFacade implements QueryFacadeInterface
{
	use TraitSingleton;
	
	public function query( string $sql, bool $getString = false )
	{
		return getComponents( 'query', true, true )->query( $sql, $getString );
	}

	public function resultArray ( string $sql ) : array
	{
		return getComponents( 'query', true, true )->resultArray( $sql );
	}
}