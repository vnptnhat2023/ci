<?php

declare(strict_types = 1);
namespace Red2Horse\Facade\Event;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class EventFacade implements EventFacadeInterface
{
    use TraitSingleton;

    public function trigger ( string $name, ...$args ) : bool
    {
        return getComponents( 'event', true, true )->trigger( $name, ...$args );
    }

    public function on ( string $eventName, $callback, int $priority = 100 ) : void
    {
        getComponents( 'event', true, true )->on( $eventName, $callback, $priority );
    }
}