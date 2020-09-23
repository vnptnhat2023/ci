<?php

namespace App\Libraries\Ext;

abstract class AbstractExtension implements InterfaceExtension {

  final private function __construct ( $params = null )
  {
		if ( is_array( $params ) )
		{
			helper('array');

			$params = isAssoc( $params ) ? [ $params ] : $params;
			$this->setParameter( ...$params );
		}
		else
		{
			$this->setParameter($params);
		}
  }

  final public function setParameter ( $params = null ) : self
  {
    $this->getParameter = $params;

    return $this;
  }

	/** @return mixed self::getParameter */
	final public function getParameter ()
	{
		return $this->getParameter;
	}

	/**
	 * @return static
	 */
  final public static function getInstance ( $params = null ) : self
  {
		$class = static::class;

    if ( ! empty( $class::$getInstance ) ) {
			return $class::$getInstance;
		}

		$lastNamespace = null;

		if ( ( $pos = strrpos( $class, '\\' ) ) !== false ) {
			$lastNamespace = substr( $class, $pos + 1 );
		}

		if ( ! array_key_exists( $lastNamespace, runExtension()->getLoaded() ) ) {
			# Write more: Evs::Trigger('extStore'); But without 2nd parameter
			throw new \Exception( lang( 'Extension.notProcessed', [ $lastNamespace ?: $class ] ) );
		}

    return $class::$getInstance = new $class( $params );
	}

	# abstract public static function getMap( string $key = null ) : array;

	# public static function getRoutes(RouteCollection $routes) : void;

  final private function __clone () {}

	final private function __wakeup () {}
}