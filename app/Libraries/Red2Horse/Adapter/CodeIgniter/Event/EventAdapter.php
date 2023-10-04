<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Event;

use CodeIgniter\Events\Events;
use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class EventAdapter implements EventAdapterInterface
{
    use TraitSingleton;

    public function on ( string $eventName, $callback, int $priority = 100 ) : void
    {
        Events::on( $eventName, $callback, $priority );
    }

    public function trigger ( string $name, ...$args ) : bool
    {
        return Events::trigger( $name, ...$args );
    }
}