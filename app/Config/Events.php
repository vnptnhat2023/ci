<?php namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on( 'pre_system', function ()
{
	if (ENVIRONMENT !== 'testing')
	{
		if (ini_get('zlib.output_compression'))
		{
			throw FrameworkException::forEnabledZlibOutputCompression();
		}

		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		ob_start(function ($buffer) {
			return $buffer;
		});

	}

	/*
	 * --------------------------------------------------------------------
	 * Debug Toolbar Listeners.
	 * --------------------------------------------------------------------
	 * If you delete, they will no longer be collected.
	 */
	if (ENVIRONMENT !== 'production')
	{
		Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
		Services::toolbar()->respond();
	}

	// Events::trigger('extStore');
	/*
	pre_system Called very early during system execution.
	Only the benchmark and events class have been loaded at this point.
	No routing or other processes have happened.

	post_controller_constructor Called immediately
	after your controller is instantiated,
	but prior to any method calls happening.

	post_system Called after the final rendered page is sent to the browser,
	at the end of system execution after the finalized data is sent to the browser.

	email Called after an email sent successfully from CodeIgniter\Email\Email.
	Receives an array of the Email class’s properties as a parameter.
	*/
} );


Events::on( 'extStore', function(
	string $Name = null,
	$params = null,
	callable $Callable = null,
	bool $overrideParam = true
)
{
	// $start = microtime( true );
	# --- Cache all enabled extensions
	if ( $storeData = model('\App\Models\Extension')->enabled() ) {
		$store = $storeData['full'];

		# --- Todo: find ext haven't event-name => instance them
		# --- Todo: make another EVENT to do that; (extStoreWithoutEvents, event-name: afterControllersCreated)
		if ( ! $Name ) { return null; }

		# Format to space-to-dash-name
		$Name = url_title( $Name, '-', true );
		# Find all the classes relation with $Name
		$keys = array_keys( array_column( $store, 'event_name' ) , $Name );

		if ( ! empty( $keys ) ) {
			helper('string');

			static $extLoaded = [];
			$extCreated = [];
			$extNotFound = [];

			foreach ( $keys as $key ) {
				# Format extension-className to camelCase with "UpperFirstLetter"
				$fileName = strCamelCase( $store[ $key ][ 'slug' ] );
				$methodName = $store[ $key ][ 'method' ];

				# Make sure just store an extension once time
				# But ... when it exists: stores methods within them
				if ( array_key_exists( $fileName, $extLoaded ) )
				{
					$extCreated[ $fileName ][ $methodName ] = null;
					continue;
				}
				# Saved loaded
				else if ( class_exists( "\\Ext\\{$fileName}\\{$fileName}" ) )
				{
					$extCreated[ $fileName ][ $methodName ] = null;
					$extLoaded[ $fileName ] = null;

					// if (CI_DEBUG)
					// {
					// 	static::$performanceLog[] = [
					// 		'start' => $start,
					// 		'end'   => microtime(true),
					// 		'event' => 'extstore___' . strtolower( $fileName ),
					// 	];
					// }
				}
				# Saved not found
				else
				{
					$extNotFound[] = $fileName;
				}
			}

			if ( ! empty( $extNotFound ) ) {
				$str = implode( ', ', $extNotFound );
				log_message('error', "The EXTENSION: {$str} not found");
			}

			if ( ! empty( $extCreated ) ) {
				# Initialize
				\App\Libraries\Extension::getInstance( $extCreated );

				# Passing parameters to instances
				if ( ! empty( $params ) ) {
					$service = service('extension');

					foreach ( $extCreated as $extClassName => $methods )
					{
						if ( true === $overrideParam )
						{
							$service->$extClassName->setParameter( $params );
						}
						else
						{
							$service->$extClassName( $params );
						}
					}
				}

				# Send back to callable all extension-classes has loaded
				if ( is_callable( $Callable ) ) { $Callable( $extCreated ); }
			}
		}
	}
} );

Events::on( 'Red2HorseAuthRegenerate', function ( $userData ) {
	Services::Red2HorseAuth()->regenerateSession( $userData );
} );