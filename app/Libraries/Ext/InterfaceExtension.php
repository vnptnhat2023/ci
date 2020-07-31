<?php namespace App\Libraries\Ext;

interface InterfaceExtension
{
  function __construct( $params = null );

  public function constructor( $params = null ) : self;

	/**
	 * @return static::$getInstance
	 */
  public static function getInstance( $params = null ) : self;

	// abstract public static function getMap( string $key = null ) : array;
	// public static function getRoutes(RouteCollection $routes) : void;

  function __clone();

	function __wakeup();

	/**
	 * @return mixed self::getParameters
	 */
	public function getParameters();
}