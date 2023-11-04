<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorClassException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\setClass;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Event
{
    use TraitSingleton;
    private     bool        $init         = false;
    private     array       $triggered    = [];

    private function __construct () {}

    /** @var mixed $callable */
    public function on ( $callable, ?string $classNamespace = null ) : void
    {
        getConfig( 'event' )->init( $callable, $classNamespace );
    }

    public function trigger ( string $name, ...$args ) : bool
    {
        $this->init();
        $eventConfig = getConfig( 'event' );

        if ( ! $eventConfig->manyTrigger && in_array( $name, $this->triggered ) )
        {
            // helpers( [ 'message' ] );
            // setErrorMessage( sprintf( 'Event: "%s" is not triggered', $name ) );
            return false;
        }

        $this->triggered[]      = $name;
        $name                   = $eventConfig->underString( $name );

        if ( array_key_exists( $name, $eventConfig->events ) )
        {
            return $this->{ $name }( ...$args );
        }

        return false;
    }

    public function init () : void
    {
        $events = getConfig( 'event' )->events;

        if ( $this->init || empty( $events ) )
        {
            return;
        }

        $this->init         = true;
        $eventComponent     = getComponents( 'event' );

        foreach( $events as $callableName => $scope )
        {
            $onData         = is_string( $scope )
                            ? [ new $scope, $callableName ]
                            : $callableName;

            $eventComponent->on( $callableName, $onData );
        }
    }

    public function reInit () : self
    {
        $this->init = false;
        $this->init();

        return $this;
    }

    public function __call ( string $methodName, array $args )
    {
        if ( array_key_exists( $methodName, getConfig( 'event' )->events ) )
        {
            $args = reset( $args );
            $common = getComponents( 'common' );

            if ( is_array( $args ) )
            {
                if ( $common->isAssocArray( $args ) )
                {
                    $eventTrigger = getComponents( 'event' )->trigger( $methodName, $args );
                }
                else
                {
                    $eventTrigger = getComponents( 'event' )->trigger( $methodName, ...$args );
                }
            }
            else
            {
                $eventTrigger = getComponents( 'event' )->trigger( $methodName, $args );
            }

            if ( ! empty( $this->triggered ) && $eventTrigger )
            {
                $eventName  = $this->triggered[ array_key_last( $this->triggered ) ];

                if ( ! setClass( $eventName, $args, false, 'RegistryEventClass' ) )
                {
                    $common->log_message(
                        'error', 
                        new ErrorClassException( sprintf( 'Function : "setClass" set failed, name: "%s"', $eventName ) ) 
                    );
                }
            }

            return $eventTrigger;
        }
    }
}