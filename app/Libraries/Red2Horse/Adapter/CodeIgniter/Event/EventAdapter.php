<?php
declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Event;

use CodeIgniter\Events\Events;

class EventAdapter implements EventAdapterInterface {
    private bool $init = false;

    /** Class::Events */
    public string $eventHelper = 'r2hEvents';
    /**
     * @property string[] $data
     */
    private array $events = [
        'BeforeLogin'
    ];

    public function trigger ( string $name, array ...$args ) : bool
    {
        $this->init();
        /** @var string $name CamelCase */
        $name = str_replace( ' ', '', ucwords( str_replace( [ '-', '_' ], ' ', $name ) ) );
        return Events::trigger( $name, ...$args );
    }

    /**
     * Adapter only
     */
    public function init() : void
    {
        if ( $this->init || empty( $this->events ) ) { return; }

        $this->init = true;
        helper( 'event' );
        $eventHelper = new $this->eventHelper;

        /** @var string $event */
        foreach( $this->events as $name )
        {
            Events::on( $name, static fn( ...$data ) => $eventHelper->$name( ...$data ) );
        }
    }

    public function reInit() : self
    {
        $this->init = false;
        $this->init();
        return $this;
    }

    public function getEvents() : array
    {
        return $this->events;
    }

    public function setEvent( array $events ) : bool
    {
        if ( empty( $events ) ) { return false; }
        $this->events = $events;
        return true;
    }

    public function addEvent( array $events ) : bool
    {
        if ( isAssoc( $events ) ) { return false; }
        $this->events = array_merge( $this->events, $events);
        return true;
    }
}