<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Common;

interface CommonFacadeInterface
{
	/**
	 * A convenience method to translate a string or array of them and format
	 * the result with the intl extension's MessageFormatter.
	 */
	public function lang( string $line, array $args = [], string $locale = null );

	public function isAssocArray( array $data ) : bool;

	/**
	 * @return mixed
	 */
	public function log_message( string $level, string $message, array $context = [] );

	/**
	 * @return array|null
	 */
	public function get_file_info( string $file, $returned_values = ['name', 'server_path', 'size', 'date'] );
}