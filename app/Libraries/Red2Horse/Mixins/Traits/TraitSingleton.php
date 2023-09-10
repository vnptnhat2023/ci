<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits;

use Red2Horse\Mixins\Classes\Registry\RegistryClass___;

/**
 * @use with TraitRegistry::class only
 */
trait TraitSingleton
{

	/** @return static */
	final public static function getInstance( $params = null, ...$args )
	{
		if ( $data = RegistryClass___::get( self::class ) )
		{
			/** @var object $instance */
			$instance = $data[ 'instance' ];
			return $instance;
		}

		$instance = new self( $params, ...$args );
		$values = [
			// 'properties' => self::_getMPs( [], false, false ),
			'methods' => self::_getMPs(),
			'instance' => $instance
		];

		RegistryClass___::set( self::class, $values );

		return $instance;
	}

	/**
	 * @example All properties : ([], false, false), all methods : (default)
	 * @param string[] $value
	 * @param bool $intersect true : ( array_diff : except ), false : array_intersect ( only )
	 * @param bool $MPs true : methods, false : properties
	 * @return mixed array */
	final public static function _getMPs( array $value = [], bool $intersect = false, bool $MPs = true ) : array
	{
		$MPs = $MPs ? get_class_methods( static::class ) : get_class_vars( static::class );

		if ( empty( $MPs ) )
		{
			return [];
		}

		$isAssoc = array_keys( $MPs ) !== range( 0, count( $MPs ) - 1 );

		if ( $isAssoc )
		{
			$MPs = array_keys( $MPs );
		}

		if ( $intersect )
		{
			$data = array_intersect( $value, $MPs );
		}
		else
		{
			$except = [
				'__construct',
				'__destruct',
				'__call',
				'__callStatic',
				'__get',
				'__set',
				'__isset',
				'__unset',
				'__sleep',
				'__wakeup',
				'__serialize',
				'__unserialize',
				'__toString',
				'__invoke',
				'__set_state',
				'__clone',
				'__debugInfo',
				'__debugBacktrace',
				'_getData',
				'_getMPs',
				'getInstance'
			];
			$data = array_diff( $MPs, $value, $except );
		}

		return array_values( $data );
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
