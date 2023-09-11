<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Event;

use CodeIgniter\Events\Events;
use Red2Horse\Mixins\Classes\Registry\RegistryEventClass;

use function Red2Horse\Mixins\Functions\setClass;

final class EventAdapter implements EventAdapterInterface
{
    private bool $init = false;

    private static self $instance;

    /**
     * @var string[] $events
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

    private array $triggered = [];

    public static function getInstance () : self
    {
        $instance = isset( self::$instance ) ? self::$instance : new self;
        return $instance;
    }

    public function trigger ( string $name, ...$args ) : bool
    {
        $this->init();

        $this->triggered[] = $name;
        $name = strtolower( trim( preg_replace( '/([A-Z]){1}/', '_$1', $name ), '_' ) );
        
        if ( in_array( $name, $this->events ) )
        {
            return Events::trigger( $name, ...$args );
        }

        return false;
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

    /** Event: r2h_before_login */
    private function r2h_before_login ( $username, $password, $remember, $captcha ) : array
    {
        // $username .= '___%*)@(%____';
        // $mes = var_export( func_get_args(), true );
        // $m = getInstance( Message::class, RegistryClass::class );
        // $m::$success[] = $mes;
        return [ $username, $password, $remember, $captcha ];
    }

    public function __call ( string $methodName, array $args )
    {
        if ( in_array( $methodName, $this->events ) )
        {
            $args = reset( $args );
            if ( $methodName === 'r2h_before_login' )
            {
                $args = $this->r2h_before_login( ...$args );
            }

            $evName = $this->triggered[ array_key_last( $this->triggered ) ];
            setClass( $evName, $args, false, RegistryEventClass::class );
        }
    }
}