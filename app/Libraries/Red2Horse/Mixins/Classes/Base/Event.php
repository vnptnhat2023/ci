<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\setClass;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Event
{
    use TraitSingleton;
    private bool $init = false;
    private array $triggered = [];

    private function __construct () {}

    /** @var mixed $callable */
    public function on ( $callable, string $classNamespace ) : void
    {
        getConfig( 'event' )->init( $callable, $classNamespace );
    }

    public function trigger ( string $name, ...$args ) : bool
    {
        $this->init();
        $eventConfig = getConfig( 'event' );

        if ( ! $eventConfig->manyTrigger && in_array( $name, $this->triggered ) )
        {
            return false;
        }

        $this->triggered[] = $name;
        $name = $eventConfig->underString( $name );

        if ( array_key_exists( $name, $eventConfig->events ) )
        {
            $this->{ $name }( ...$args );
        }

        return false;
    }

    public function init () : void
    {
        $events = getConfig( 'event' )->events;
        if ( $this->init || empty( $events ) ) { return; }

        $this->init = true;
        $eventComponent = getComponents( 'event' );

        foreach( $events as $callableName => $scope )
        {
            $onData = is_string( $scope ) ? [ new $scope, $callableName ] : $callableName;
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

            if ( ! empty( $this->triggered ) && getComponents( 'event' )->trigger( $methodName, $args ) )
            {
                $evName = $this->triggered[ array_key_last( $this->triggered ) ];
                setClass( $evName, $args, false, 'RegistryEventClass' );
            }
        }
    }
}