<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

use CodeIgniter\Events\Events;
use CodeIgniter\Files\Exceptions\FileNotFoundException;

if (! function_exists('bapiView'))
{
	function bView(string $name, array $data = [], array $options = []): string
	{
    $renderer = \Config\Services::renderer(BACKENDPATH .'/Views/', null, false);

		$saveData = true;
		if (array_key_exists('saveData', $options) && $options['saveData'] === true)
		{
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}

		return $renderer->setData($data, 'raw')
						->render($name, $options, $saveData);
	}
}

if (! function_exists('fapiView'))
{
	function fView(string $name, array $data = [], array $options = []): string
	{
    $renderer = \Config\Services::renderer(FRONTENDPATH .'/Views/', null, false);

		$saveData = true;
		if (array_key_exists('saveData', $options) && $options['saveData'] === true)
		{
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}

		return $renderer->setData($data, 'raw')
						->render($name, $options, $saveData);
	}
}

if (! function_exists('runExtension'))
{
	/**
	 * @param string $className The class-name handle the current extension
	 * @param mixed $params
	 * @return mixed AbstractExtension | Extension | null
	 */
	function runExtension(string $className = null, $params = null)
	{
		$serviceExtension = service('extension');

		if ( empty( $className ) ) { return $serviceExtension; }

		$className = ucfirst($className);

		if ( empty( $params ) ) { return $serviceExtension->$className; }

		if ( null === $serviceExtension->$className ) {
			return null;
			// throw FileNotFoundException::forFileNotFound($className);
		}

		return $serviceExtension->$className->setParameter($params);
	}
}

if ( ! function_exists('handleExtension')) {
	/**
	 * Run an event and handle the entire class inside it
	 *
	 * ---
	 *
	 * @param string $eventName *event-name-dash-separate*
	 * @param mixed $params
	 * @param bool $getReturn *default false*
	 * - **false** return (void)
	 * - **true** return mixed (array | null) null: when not exist
	 * @param bool $overrideParam *default true*
	 * - **true** override __construct parameters
	 * - **false** passing to current entire method inside it
	 * @return mixed void | array | null
	 */
	function handleExtension(
		string $eventName,
		$params = null,
		bool $getReturn = false,
		bool $overrideParam = true
	) {
		if ( false === $getReturn )
		{
			Events::trigger( 'extStore', $eventName, $params, null, $overrideParam );
		}
		else
		{
			$classInstances = [];

			$eventInit = function( $classesLoaded ) use( &$classInstances, $params, $overrideParam ) {
				# Check method if exist inputManager, route, component, ...
				foreach ( $classesLoaded as $class => $methods ) {

					if ( !empty( $methods ) ) {
						foreach ( $methods as $method => $itNull ) {

							$key = "{$class}::{$method}";

							if ( true === $overrideParam )
							$classInstances[ $key ] = runExtension( $class, $params )->$method();

							else
							$classInstances[ $key ] = runExtension( $class )->$method($params);
						}
					}
				}
			};

			Events::trigger( 'extStore', $eventName, $params, $eventInit, $overrideParam );

			return $classInstances;
		}
	}
}