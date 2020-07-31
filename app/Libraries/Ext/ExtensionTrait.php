<?php
namespace App\Libraries\Ext;

trait ExtensionTrait
{
	/**
	 * Singleton pattern
	 * @var \App\Libraries\Ext\AbstractExtension $getInstance
	 */
	protected static $getInstance;

	/**
	 * Receive data from global **function** ( handleExtension & runExtension )
	 * @var mixed $getParameters
	 */
	protected $getParameters;

	/**
	 * Map from the database to this extension
	 * @param string|null $key
	 * @return mixed static::map[$key] | static::map | []
	 */
	final public static function getMap(string $key = null)
	{
		$class = static::class;

		if ( false === defined( "{$class}::map" ) ) {
			throw new \Exception( lang(
				'Extension.constantNotDefined', [ 'class' => $class, 'constant' => 'map' ]
			) );
		}

		return dot_array_search($key, $class::map);
	}
}