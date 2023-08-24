<?php
declare(strict_types = 1);
namespace Red2Horse\Facade\Event;

use Red2Horse\{
    Mixins\TraitSingleton
};

class EventFacade implements EventFacadeInterface
{
    use TraitSingleton;

    protected EventFacadeInterface $event;

	public function __construct( EventFacadeInterface $event )
	{
		$this->event = $event;
	}

    public function trigger ( string $name, array ...$args ) : bool
    {
        return $this->event->trigger( $name, ...$args );
    }
}