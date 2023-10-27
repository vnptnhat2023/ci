<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\CodeIgniter\Common;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CommonAdapter implements CommonAdapterInterface
{
	use TraitSingleton;
	public function cache( ?string $key = null )
	{
		return cache( $key );
	}

	public function lang( string $line, array $args = [], string $locale = null )
	{
		return lang( $line, $args, $locale );
	}

	public function get_file_info(
		string $file,
		$returned_values = [ 'name', 'server_path', 'size', 'date' ]
	)
	{
		helper( 'filesystem' );
		return get_file_info( $file, $returned_values );
	}

	public function isAssocArray( array $data ) : bool
	{
		if ( [] === $data )
		{
			return false;
		}
	
		return array_keys( $data ) !== range( 0, count( $data ) - 1 );
	}

	public function log_message( string $level, string $message, array $context = [] )
	{
		return log_message( $level, $message, $context );
	}
}