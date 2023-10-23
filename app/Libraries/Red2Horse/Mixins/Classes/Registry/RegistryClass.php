<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Registry;

use Red2Horse\Mixins\Classes\Object\CallClass___;
use Red2Horse\Mixins\Traits\Registry\TraitRegistryClassMethod;

use function Red2Horse\Mixins\Functions\NS\ComponentNamespace;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

final class RegistryClass extends RegistryClass___
{
    use TraitRegistryClassMethod;

    private bool $getShared;
    private string $className;

    public static self $instance;

    public function __construct ( string $state = self::class )
    {
        $this->configRegistryClassMethod___( $state );
    }

    /**
     * Singleton itself
     */
    public static function selfInstance ( string $state = self::class ) : self
    {
        self::$instance = isset( self::$instance ) ? self::$instance : new self( $state );
        return self::$instance;
    }

    public function init ( string $className, bool $getShared = true )
    {
        $this->getShared = $getShared;
        $this->className = $className;

        $this->configRegistryClassMethod___( self::class );

        return $this;
    }

    /**
     * @return self|\Red2Horse\Mixins\Traits\Object\TraitSingleton
     */
    public function getInstance ( $params = null )
    {
        if ( self::class == $this->className )
        {
            return self::selfInstance( $params );
        }

        if ( $this->getShared )
        {
            $classData = $this->instanceData( $this->className, $params );
            return $classData[ 'instance' ];
        }

        return new $this->className( $params );
    }

    /**
     * @param string $name name only ( not namespace )
     * @param bool $getShared adapter only
     * @return object|\Red2Horse\Mixins\Traits\Object\TraitSingleton
     */
    public function getComponents ( bool $getAdapter = false ) : object
    {
        $name = ucfirst( $this->className );
        $facadeName = ComponentNamespace( $name );

        if ( in_array( $name, [ 'User', 'Throttle' ], true ) )
        {
            $adapterName = ComponentNamespace( 'Database', 'adapter', $name );
            $facadeName = ComponentNamespace( 'Database', 'facade', $name );
        }
        else
        {
            $adapterName = ComponentNamespace( $name, 'adapter' );
        }

        if ( $this->getShared && method_exists( $adapterName, 'getInstance' ) )
        {
            $adapterInstance = $adapterName::getInstance();
        }
        else
        {
            $adapterInstance = new $adapterName;
        }

        if ( $getAdapter )
        {
            return $adapterInstance;
        }

        return $facadeName::getInstance( $adapterInstance );
    }

    public function getInstanceMethods ( $params = null ) : array
    {
        if ( $this->getShared )
        {
            $classData = $this->instanceData( $this->className, $params );
            return $classData[ 'methods' ];
        }

        return get_class_methods( $this->className );
    }

    /**
     * @param bool $getShared true: callClass___ class; false: anonymous class
     * @return mixed
     */
    public function callClass ( array $arguments = [] )
    {
        if ( $this->getShared )
        {
            $instance = CallClass___::getInstance( $this->className, $arguments );
        }
        else
        {
            $instance = new class( $this->className, $arguments )
            {
                use \Red2Horse\Mixins\Traits\Object\TraitCall;

                public function __construct( string $className, array $arguments )
                {
                    $this->run( $className );
                }
            };
        }

        return $instance->__call( $arguments[ 'method_name' ], $arguments[ 'arguments' ] );
    }
}