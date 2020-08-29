<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Common;

class CommonFacade implements CommonFacadeInterface
{
	protected CommonFacadeInterface $common;

	public function __construct ( CommonFacadeInterface $common )
	{
		$this->common = $common;
	}

	public function lang( string $line, array $args = [], string $locale = null )
	{
		return $this->common->lang( $line, $args, $locale );
	}

	public function isAssocArray( array $data ) : bool
	{
		return $this->common->isAssocArray( $data );
	}

	/**
	 * @return mixed
	 */
	public function log_message( string $level, string $message, array $context = [] )
	{
		return $this->common->log_message( $level, $message, $context );
	}

	public function get_file_info(
		string $file,
		$returned_values = ['name', 'server_path', 'size', 'date']
	)
	{
		return $this->common->get_file_info( $file, $returned_values );
	}
}