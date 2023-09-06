<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Event;

use CodeIgniter\Events\Events;

final class EventAdapter implements EventAdapterInterface
{
    private bool $init = false;

    private static self $instance;

    /**
     * @property string[] $data
     */
    private array $events = [
        'r2h_before_get_message',
        'r2h_after_get_message',

        'r2h_before_is_logged',
        'r2h_after_is_logged',

        'r2h_before_get_result',
        'r2h_after_get_result',

        'r2h_before_login',
        'r2h_after_login',

        'r2h_before_logout',
        'r2h_after_logout',

        'r2h_before_request_password',
        'r2h_after_request_password',
    ];

    public static function getInstance () : self
    {
        $instance = isset( self::$instance ) ? self::$instance : new self;
        return $instance;
    }

    public function trigger ( string $name, ...$args ) : bool
    {
        $this->init();
        $name = strtolower( trim( preg_replace( '/([A-Z]){1}/', '_$1', $name ), '_' ) );
        return Events::trigger( $name, ...$args );
    }

    public function init () : void
    {
        if ( $this->init || empty( $this->events ) ) { return; }

        $this->init = true;

        /** @var string $event */
        foreach( $this->events as $event )
        {
            Events::on( $event, [ $this, $event ] );
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
        if ( empty( $events ) ) { return false; }
        $this->events = $events;
        $this->reInit();
        return true;
    }

    public function addEvent ( array $events ) : bool
    {
        if ( isAssoc( $events ) ) { return false; }
        $this->events = array_merge( $this->events, $events);
        $this->reInit();
        return true;
    }

    public function __call ( $methodName, $args )
    {
        if ( in_array( $methodName, $this->events ) )
        {
            // d( $args, $methodName );
        }
    }
}