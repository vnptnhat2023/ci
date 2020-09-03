<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Mixins;

trait TraitSingleton
{
	private static self $getInstance;

	public static function getInstance( $params = null )
	{
		if ( empty( self::$getInstance ) ) {
			return new self( $params );
		}

		return self::$getInstance;
	}

	final private function __clone() {}
}
