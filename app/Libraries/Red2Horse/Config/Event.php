<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Functions\UserDefinedFunctions;
use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Event
{
    use TraitSingleton;

    /** Many times trigger */
    public bool $manyTrigger = false;
    public bool $useBefore = true;
    public bool $useAfter = true;

    public string $prefix = 'R2h';
    public string $beforePrefix = 'before';
    public string $afterPrefix = 'after';
    
    public array $events = [];
    protected array $eventReg = [
        'get_message'         => UserDefinedFunctions::class,
        'is_logged'           => UserDefinedFunctions::class,
        'get_result'          => UserDefinedFunctions::class,
        'login'               => UserDefinedFunctions::class,
        'logout'              => UserDefinedFunctions::class,
        'request_password'    => UserDefinedFunctions::class
    ];

    private function __construct ()
    {
        $this->reInit();
    }

    public function reInit () : void
    {
        $events = [];

        foreach ( $this->eventReg as $stringCallable => $className )
        {
            if ( $this->useBefore )
            {
                $events[ $this->getPrefixNamed( $this->beforePrefix, $stringCallable ) ] = $className;
            }

            if ( $this->useAfter )
            {
                $events[ $this->getPrefixNamed( $this->afterPrefix, $stringCallable ) ] = $className;
            }
        }

        $this->events = $events;
    }

    /** @param string $abPrefix before or after prefix */
    public function getPrefixNamed ( string $abPrefix, string $stringCallable ) : string
    {
        return sprintf(
            '%s_%s_%s',
            strtolower( $this->prefix ),
            strtolower( $abPrefix ),
            strtolower( $stringCallable )
        );
    }
    
    public function underString ( string $name ) : string
    {
        return strtolower( trim( preg_replace( '/([A-Z]){1}/', '_$1', $name ), '_' ) );
    }
}