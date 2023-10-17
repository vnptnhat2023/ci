<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Functions\UserDefinedFunctions;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Event
{
    use TraitSingleton;

    /** Many times trigger */
    public bool $manyTrigger = false;
    public bool $useBefore = true;
    public bool $useAfter = true;

    /** Prefix */
    public string $prefix = 'R2h';
    public string $beforePrefix = 'before';
    public string $afterPrefix = 'after';
    
    /** Events */
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
        $this->init();
    }

    /**
     * @var mixed $key
     */
    public function init ( $key = null, ?string $classNamespace = null ) : void
    {
        if ( null !== $key && null !== $classNamespace && ! array_key_exists( $key, $this->eventReg ) )
        {
            $this->eventReg[ $key ] = $classNamespace;
        }

        $events = [];

        foreach ( $this->eventReg as $stringCallable => $className )
        {
            if ( $this->useBefore )
            {
                $beforeKey = $this->getPrefixNamed( $this->beforePrefix, $stringCallable );
                $events[ $beforeKey ] = $className;
            }

            if ( $this->useAfter )
            {
                $afterKey = $this->getPrefixNamed( $this->afterPrefix, $stringCallable );
                $events[ $afterKey ] = $className;
            }
        }

        $this->events = $events;
    }

    /** @param string $abPrefix before or after prefix */
    public function getPrefixNamed ( string $abPrefix, string $stringCallable, string $format = '%1$s_%2$s_%3$s' ) : string
    {
        return sprintf(
            $format,
            strtolower( $this->prefix ),
            strtolower( $abPrefix ),
            strtolower( $stringCallable )
        );
    }
    
    public function underString ( string $name ) : string
    {
        return getComponents( 'common' )->underString( $name );
    }
}