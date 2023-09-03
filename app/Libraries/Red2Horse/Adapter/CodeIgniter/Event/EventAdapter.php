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
        'R2hBeforeLogin',
        'R2hAfterLogin',

        'R2hBeforeLogout',
        'R2hAfterLogout'
    ];

    public function trigger ( string $name, array ...$args ) : bool
    {
        $this->init();

        $search = [' ', '\\', '/', '\'', '"'];
        $replace = [''];
        $subject = ucwords( str_replace( [ '-', '_' ], ' ', $name ) );
        /** @var string $name CamelCase */
        $name = str_replace( $search, $replace, $subject );

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

        $i = 100;

        /** @var string $event */
        foreach( $this->events as $eventName )
        {
            if ( ! method_exists( $eventHelper, $eventName ) )
            {
                $err = sprintf( '[ERROR] Invalid method trigger %s::%s', $this->eventHelper, $eventName );
                log_message( 'error', $err );
            }

            // echo '<p> class: ' . __CLASS__ . ' event name: ' . $eventName . '</p>';
            // echo '<p> class event name: ' . $eventName . '</p>';
            Events::on( $eventName, [ $eventHelper, $eventName ],  $i--);
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