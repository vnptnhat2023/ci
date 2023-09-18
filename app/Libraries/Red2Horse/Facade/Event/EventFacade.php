<?php
declare(strict_types = 1);
namespace Red2Horse\Facade\Event;

use Red2Horse\
{
    Mixins\Traits\TraitSingleton
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class EventFacade implements EventFacadeInterface
{
    use TraitSingleton;

    protected EventFacadeInterface $event;

	public function __construct( EventFacadeInterface $event )
	{
		$this->event = $event;
	}

    public function trigger ( string $name, ...$args ) : bool
    {
        return $this->event->trigger( $name, ...$args );
    }
}