<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Registry;

use Red2Horse\Mixins\Interfaces\Registry\RegistryClass___Interface;

use Red2Horse\Mixins\Traits\Registry\
{
    TraitRegistryClassMethod,
    TraitRegistry
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class RegistryEventClass implements RegistryClass___Interface
{
    use TraitRegistry, TraitRegistryClassMethod;

    public static self $instance;

    final public function __construct ( string $state = self::class )
    {
        $this->configRegistryClassMethod___( $state );
    }

    public function init() : self
    {
        return $this;
    }

    public static function selfInstance (  string $state = self::class  ) : self
    {
        self::$instance = isset( self::$instance ) ? self::$instance : new self( $state );
        return self::$instance;
    }

    public function __destruct()
    {
        // d( RegistryEventClass::getInstance() );
    }
}