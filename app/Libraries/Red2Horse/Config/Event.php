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

    /** Use */
    public          bool            $manyTrigger        = false;
    public          bool            $useBefore          = true;
    public          bool            $useAfter           = true;
    /** Prefix */
    public          string          $prefix             = 'R2h';
    public          string          $beforePrefix       = 'before';
    public          string          $afterPrefix        = 'after';
    /** Events */
    public          array           $events             = [];
    protected array $eventPrefix = [
        'get_message'      => UserDefinedFunctions::class,
        'is_logged'        => UserDefinedFunctions::class,
        'get_result'       => UserDefinedFunctions::class,
        'login'            => UserDefinedFunctions::class,
        'logout'           => UserDefinedFunctions::class,
        'request_password' => UserDefinedFunctions::class
    ];
    protected array $eventNoPrefix = [
        'message_show_captcha_condition'        => UserDefinedFunctions::class,
        'authentication_show_captcha_condition' => UserDefinedFunctions::class,
        'resetpassword_show_captcha_condition'  => UserDefinedFunctions::class
    ];

    private function __construct ()
    {
        $this->init();
    }

    /**
     * @var mixed $key
     */
    public function init ( ?string $key = null, ?string $classNamespace = UserDefinedFunctions::class, bool $withPrefix = true ) : void
    {
        if ( null !== $key && ! array_key_exists( $key, $this->eventPrefix ) )
        {
            $this->eventNoPrefix[ $key ] = $classNamespace ?: UserDefinedFunctions::class;
        }

        if ( $withPrefix )
        {
            $this->handle();
        }

        $this->events = array_merge( $this->eventPrefix, $this->eventNoPrefix );
    }

    private function handle () : void
    {
        foreach ( $this->eventPrefix as $stringCallable => $className )
        {
            if ( $this->useBefore )
            {
                $beforeKey = $this->getPrefixNamed( $this->beforePrefix, $stringCallable );
                $events[ $beforeKey ] = $className;
            }
            else
            {
                $events[ $stringCallable ] = $className;
            }

            if ( $this->useAfter )
            {
                $afterKey = $this->getPrefixNamed( $this->afterPrefix, $stringCallable );
                $events[ $afterKey ] = $className;
            }
            else
            {
                $events[ $stringCallable ] = $className;
            }
        }

        $this->eventPrefix = $events;
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