<?php

declare( strict_types = 1 );

namespace Red2Horse\Mixins\Traits;

use function Red2Horse\Mixins\Functions\
{
    getComponents,
    getInstance,
    getInstanceMethods
};

/** Using with TraitSingleton::class only ... */
trait TraitCall
{
	private object $traitCallInstance;

	private array $traitCallMethods = [];
    
    private array $traitCallback = [
        'callback' => null,
        'arguments' => [],
        'before' => false,
        'after' => false
    ];

    private string $traitBeforePrefix = 'R2h_before_';
    private string $traitAfterPrefix = 'R2h_after_';

    public function run ( string $className = '' ) : void
    {
        $this->traitCallInstance = getInstance( $className );
        $this->traitCallMethods = getInstanceMethods( $className );
        $this->traitCallback[ 'callback' ] = function( string $name, $args ) : bool
        {
            return getComponents( 'event' )->trigger( $name, $args );
        };
    }

    public function __call ( string $method = '', array $arguments )
    {
        if ( in_array( $method, $this->traitCallMethods, true ) )
        {
            $beforeName = $this->traitBeforePrefix . $method;
            $afterName = $this->traitAfterPrefix . $method;
            $callback = $this->traitCallback[ 'callback' ] ?? false;

            if ( is_callable( $callback ) )
            {
                $callbackArgs = ! empty( $this->traitCallback[ 'arguments' ] )
                    ? array_merge( $arguments, $this->traitCallback[ 'arguments' ] )
                    : $arguments;

                if ( $this->traitCallback[ 'before' ] )
                {
                    /** @var bool $callback */
                    $callback( $beforeName, $callbackArgs );
                }

                /** @var mixed $run */
                $run = $this->traitCallInstance->$method( ...$callbackArgs );

                if ( $this->traitCallback[ 'after' ] )
                {
                    /** @var bool $callback */
                    $callback( $afterName, $run );
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

    public function trigger ( \Closure $closure, string $event, array $eventData )
	{
		if ( ! isset( $closure->{ $event } ) || empty( $closure->{ $event } ) )
		{
			return $eventData;
		}

		foreach ( $closure->{ $event } as $callback )
		{
			if ( ! method_exists( $closure, $callback ) )
			{
				throw new \Exception( 'Invalid Method Triggered', 403 );
			}

			$eventData = $closure->{ $callback }( $eventData );
		}

		return $eventData;
	}
}