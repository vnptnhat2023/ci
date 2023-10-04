<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits;

use Red2Horse\Facade\Auth\Event;
use Red2Horse\Mixins\Classes\Registry\RegistryEventClass;

use function Red2Horse\Mixins\Functions\
{
    getClass,
    getComponents,
    getConfig,
    getInstance,
    getInstanceMethods
};

/** Using with TraitSingleton::class only ... */
trait TraitCall
{
	private object $traitCallInstance;

	private array $traitCallMethods;
    
    private array $traitCallback = [
        'callback' => null,
        'arguments' => []
    ];

    private bool $traitUseBefore;
    private bool $traitUseAfter;
    private string $traitBeforePrefix;
    private string $traitAfterPrefix;

    public function run ( string $className = '' ) : void
    {
        $eventConfig = getConfig( 'event' );

        $this->traitUseBefore ??= $eventConfig->useBefore;
        $this->traitUseAfter ??= $eventConfig->useAfter;
        $this->traitBeforePrefix ??= $eventConfig->beforePrefix;
        $this->traitAfterPrefix ??= $eventConfig->afterPrefix;

        $this->traitCallInstance = getInstance( $className );
        $this->traitCallMethods = getInstanceMethods( $className );
        $this->traitCallback[ 'callback' ] = function( string $name, $args ) : bool
        {
            return getInstance( Event::class )->trigger( $name, $args );
        };
    }

    public function __call ( string $method, array $arguments )
    {
        if ( in_array( $method, $this->traitCallMethods, true ) )
        {
            $beforeName = $this->traitBeforePrefix . $method;
            $afterName = $this->traitAfterPrefix . $method;
            $callback = $this->traitCallback[ 'callback' ] ?? null;

            if ( is_callable( $callback ) )
            {
                $callbackArgs = $arguments;
                
                if ( ! empty( $this->traitCallback[ 'arguments' ] ) )
                {
                    if ( isset( $arguments[ 0 ] ) && is_array( $arguments[ 0 ] ) )
                    {
                        $common = getComponents( 'common' );

                        if ( $common->isAssocArray( $this->traitCallback[ 'arguments' ] ) )
                        {
                            $merge = array_merge( $arguments[ 0 ], $this->traitCallback[ 'arguments' ] );
                            $callbackArgs = [ $merge ];
                        }
                        else
                        {
                            $common->log_message(
                                'warning', $common->lang( 'isAssoc' ) 
                                    . sprintf( ' File: %s, Line: %s', __FILE__, __LINE__ )
                            );
                        }
                    }
                    else
                    {
                        $callbackArgs = empty( $arguments )
                            ? $arguments
                            : array_merge( $arguments, $this->traitCallback[ 'arguments' ] );
                    }
                }

                if ( $this->traitUseBefore && $callback( $beforeName, $callbackArgs ) )
                {
                    $callbackArgs = getClass( $beforeName, '', RegistryEventClass::class );
                }

                /** @var mixed $run */
                $run = $this->traitCallInstance->{ $method }( ...$callbackArgs );

                if ( $this->traitUseAfter && $callback( $afterName, $run ) )
                {
                    $run = getClass( $afterName, '', RegistryEventClass::class );
                }

                return $run;
            }

            return $this->traitCallInstance->$method( ...$arguments );
        }

        $error = sprintf(
            '{ ( %s )-> %s() } [ File: %s ] ( Line: %s )',
            self::class, $method, __FILE__, __LINE__
        );

        throw new \BadMethodCallException( $error, 403 );
	}

    // public function trigger ( \Closure $closure, string $event, array $eventData )
	// {
	// 	if ( ! isset( $closure->{ $event } ) || empty( $closure->{ $event } ) )
	// 	{
	// 		return $eventData;
	// 	}

	// 	foreach ( $closure->{ $event } as $callback )
	// 	{
	// 		if ( ! method_exists( $closure, $callback ) )
	// 		{
	// 			throw new \Exception( 'Invalid Method Triggered', 403 );
	// 		}

	// 		$eventData = $closure->{ $callback }( $eventData );
	// 	}

	// 	return $eventData;
	// }
}