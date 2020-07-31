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

Events::on('pre_system', function () {
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

	Events::trigger('extStore');
});

Events::on( 'extStore', function(
	string $Name = null,
	$params = null,
	callable $Callable = null,
	bool $overrideParam = true
) {
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
				# Format extension-className to camelCase with "UpperFisrtLetter"
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

					foreach ( $extCreated as $extClassName => $methods ) {
						if ( true === $overrideParam )
							$service->$extClassName->constructor( $params );
						else
							$service->$extClassName( $params );
					}
				}

				# Send back to callable all extension-classes has loaded
				if ( is_callable( $Callable ) ) { $Callable( $extCreated ); }
			}
		}
	}
} );