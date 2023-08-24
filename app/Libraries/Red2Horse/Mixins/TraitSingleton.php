<?php
declare( strict_types = 1 );
namespace Red2Horse\Mixins;

trait TraitSingleton
{
	private static self $getInstance;
	/**
	 * @return static
	 */
	final public static function getInstance( $params = null, ...$args )
	{
		if ( empty( self::$getInstance ) ) {
			$i = new self( $params, ...$args );
			return $i;
		}

		return self::$getInstance;
	}

	/**
	 * @param bool $intersect true : array_diff, false = array_intersect
	 * @return mixed array */
	final public static function getMethods( array $value = [], bool $intersect = false ) : array
	{
		$methods = get_class_methods( static::class );
		if ( empty( $methods ) ) { return []; }

		if ( $intersect )
		{
			return  array_intersect( $value, $methods );
		}

		return array_diff( $methods, $value );
	}

	final private function __clone() {}

	public function __debugInfo ()
	{
		return call_user_func( 'get_object_vars', $this );
	}

	public function __debugBacktrace ( int $limit = 20) : array
	{
		return debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit );
	}
}
