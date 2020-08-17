<?php

declare( strict_types = 1 );

namespace App\Libraries\DesignPattern;

class Registry
{
	protected static array $data = [];

	public static function set ( string $key, $value )
	{
		self::$data[ $key ] = $value;
	}

	public static function get ( string $key )
	{
		return self::$data[ $key ] ?? null;
	}

	final public static function delete ( string $key )
	{
		if ( array_key_exists( $key, self::$data ) ) {
			unset( self::$data[ $key ] );

			return true;
		}

		return false;
	}
}