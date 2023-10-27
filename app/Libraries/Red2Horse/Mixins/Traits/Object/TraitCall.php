<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits\Object;

use Red2Horse\Exception\ErrorMethodException;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getClass;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Instance\getInstanceMethods;

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
            return getBaseInstance( 'event' )->trigger( $name, $args );
        };
    }

    public function __call ( string $method, array $arguments )
    {
        if ( in_array( $method, $this->traitCallMethods, true ) )
        {
            $eventConfig = getConfig( 'event' );
            $beforeName = $eventConfig->getPrefixNamed(
                $this->traitBeforePrefix,
                $eventConfig->underString( $method )
            );
            $afterName = $eventConfig->getPrefixNamed(
                $this->traitAfterPrefix,
                $eventConfig->underString( $method )
            );

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
                    $callbackArgs = getClass( $beforeName, '', 'RegistryEventClass' );
                }

                /** @var mixed $run */
                $run = $this->traitCallInstance->{ $method }( ...$callbackArgs );

                if ( $this->traitUseAfter && $callback( $afterName, $run ) )
                {
                    $run = getClass( $afterName, '', 'RegistryEventClass' );
                }

                return $run;
            }

            return $this->traitCallInstance->$method( ...$arguments );
        }

        throw new ErrorMethodException( sprintf( 'Method: __CALL()::%s()', $method ) );
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