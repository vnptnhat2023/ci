<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Common;

class CommonFacade implements CommonAdapterInterface
{
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
		return isAssoc( $data );
	}

	public function log_message( string $level, string $message, array $context = [] )
	{
		return log_message( $level, $message, $context );
	}
}