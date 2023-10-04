<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Common;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\getConfig;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CommonFacade implements CommonFacadeInterface
{
	use TraitSingleton;

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

	public function valid_json( string $str = null ) : bool
	{
		json_decode( $str );
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Create a Random String
	 *
	 * Useful for generating passwords or hashes.
	 *
	 * @package CodeIgniter-4-text_helper
	 * @param string  $type Type of random string.  basic, alpha, alnum, numeric, nozero, md5, sha1, and crypto
	 * @param integer $len  Number of characters
	 *
	 * @return string
	 */
	public function random_string ( string $type = 'alnum', int $len = 8 ) : string
	{
		switch ($type)
		{
			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
				switch ($type)
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric':
						$pool = '0123456789';
						break;
					case 'nozero':
						$pool = '123456789';
						break;
				}

				return substr( str_shuffle( str_repeat( $pool, (int) ceil(  $len / strlen( $pool ) ) ) ), 0, $len );
			case 'md5':
				return md5( uniqid( ( string ) mt_rand(), true ) );
			case 'sha1':
				return sha1( uniqid( ( string ) mt_rand(), true ) );
			case 'crypto':
				return bin2hex( random_bytes( $len / 2 ) );
		}

		// 'basic' type treated as default
		return (string) mt_rand();
	}

	function camelCase ( string $str, bool $ucfirst = false ) : string
	{
		$str = str_replace( ' ', '', ucwords( str_replace( [ '-', '_' ], ' ', $str ) ) );

		if ( ! $ucfirst )
		{
			$str[ 0 ] = strtolower( $str[ 0 ] );
		}

		return $str;
	}

	public function esc ( string $str ) : string
	{
		$isEscape = getConfig( 'sql' )->esc;
		return $isEscape ? str_replace( ['\'', '\"'], ['\'\'', '\"\"'], $str ) : $str;
	}
}