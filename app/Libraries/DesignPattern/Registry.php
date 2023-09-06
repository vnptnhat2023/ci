<?php

declare( strict_types = 1 );

namespace App\Libraries\DesignPattern;

class Registry
{
	protected static array $data = [];

	/**
	 * @param mixed $value
	 */
	final public static function set ( string $key, $value ) : void
	{
		self::$data[ $key ] = $value;
	}

	/**
	 * @return mixed
	 */
	final public static function get ( string $key )
	{
		return self::$data[ $key ] ?? null;
	}

	final public static function delete ( string $key ) : bool
	{
		if ( array_key_exists( $key, self::$data ) ) {
			unset( self::$data[ $key ] );

			return true;
		}

		return false;
	}
}