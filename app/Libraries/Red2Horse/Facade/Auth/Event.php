<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\
{
    Traits\TraitSingleton,
    Classes\Registry\RegistryEventClass
};

use function Red2Horse\Mixins\Functions\
{
    getComponents,
    getConfig,
    setClass
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Event
{
    use TraitSingleton;
    private bool $init = false;

    /** @var string[] $events */
    private array $events = [];

    private array $triggered = [];

    public function trigger ( string $name, ...$args ) : bool
    {
        $this->init();
        $eventConfig = getConfig( 'event' );

        if ( ! $eventConfig->manyTrigger && in_array( $name, $this->triggered ) )
        {
            return false;
        }

        $this->triggered[] = $name;
        $name = strtolower( trim( preg_replace( '/([A-Z]){1}/', '_$1', $name ), '_' ) );

        if ( array_key_exists( $name, $eventConfig->events ) )
        {
            getComponents( 'event' )->trigger( $name, ...$args );
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

    public function getEvents () : array
    {
        return $this->events;
    }

    public function setEvent ( array $events ) : bool
    {
        if ( empty( $events ) )
        {
            return false;
        }

        $this->events = $events;
        $this->reInit();

        return true;
    }

    public function addEvent ( array $events ) : bool
    {
        if ( isAssoc( $events ) )
        {
            return false;
        }

        $this->events = array_merge( $this->events, $events);
        $this->reInit();

        return true;
    }

    public function __call ( string $methodName, array $args )
    {
        if ( array_key_exists( $methodName, getConfig( 'event' )->events ) )
        {
            $args = reset( $args );

            if ( ! empty( $this->triggered ) )
            {
                $evName = $this->triggered[ array_key_last( $this->triggered ) ];
                setClass( $evName, $args, false, RegistryEventClass::class );
            }
        }
    }
}