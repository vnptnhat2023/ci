<?php

namespace App\Libraries\Ext;

interface InterfaceExtension
{

  public function setParameter ( $params = null ) : self;

	/** @return mixed self::getParameter */
	public function getParameter ();

	/** @return static::$getInstance */
	public static function getInstance ( $params = null ) : self;

	# public static function getMap( string $key = null ) : array;
	# public static function getRoutes(RouteCollection $routes) : void;
}