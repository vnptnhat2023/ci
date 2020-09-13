<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Mixins;

trait TraitSingleton
{
	private static self $getInstance;

	/**
	 * @return static
	 */
	final public static function getInstance( $params = null, ...$args )
	{
		if ( empty( self::$getInstance ) ) {
			return new self( $params, ...$args );
		}

		return self::$getInstance;
	}

	final private function __clone()
	{
	}

	public function __debugInfo ()
	{
		return call_user_func( 'get_object_vars', $this );
	}
}
