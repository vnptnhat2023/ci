<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Event;

interface EventFacadeInterface {
    public function trigger ( string $name, array ...$args ) : bool;
}