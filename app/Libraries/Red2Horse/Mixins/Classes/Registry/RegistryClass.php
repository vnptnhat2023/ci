<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Registry;

use Red2Horse\Mixins\Classes\CallClass___;
use Red2Horse\Mixins\Traits\Registry\TraitRegistryClassMethod;

use function Red2Horse\Mixins\Functions\getConfig;

final class RegistryClass extends RegistryClass___
{
    use TraitRegistryClassMethod;

    private bool $getShared;
    private string $className;

    public static self $instance;

    final public function __construct ( string $state = self::class )
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

        $this->configRegistryClassMethod___( parent::class );

        return $this;
    }

    /**
     * @return object (new $this->className)
     */
    public function getInstance ()
    {
        if ( $this->getShared )
        {
            $classData = $this->instanceData( $this->className );
            return $classData[ 'instance' ];
        }

        return new $this->className;
    }

    /**
     * @param string $name name only ( not namespace )
     * @param bool $getShared adapter only
     */
    public function getComponents () : object
    {
        $config = $this->instanceData( \Red2Horse\Config\BaseConfig::class )[ 'instance' ];
        // $config = getConfig();
        $name = ucfirst( $this->className );
        $facadeName = $config->facade( $name );

        if ( in_array( $name, [ 'User', 'Throttle' ], true ) )
        {
            $adapterName = $config->adapter( 'Database', $name );
            $facadeName = $config->facade( 'Database', $name );
        }
        else
        {
            $adapterName = $config->adapter( $name );
        }
        // dd( $adapterName, $facadeName );

        if ( $this->getShared && method_exists( $adapterName, 'getInstance' ) )
        {
            $adapterInstance = $adapterName::getInstance();
        }
        else
        {
            $adapterInstance = new $adapterName;
        }

        return $facadeName::getInstance( $adapterInstance );
    }

    public function getInstanceMethods () : array
    {
        if ( $this->getShared )
        {
            $classData = $this->instanceData( $this->className );
            return $classData[ 'methods' ];
        }

        return get_class_methods( $this->className );
    }

    /**
     * @param bool $getShared true: callClass___ class; false: anonymous class
     */
    public function callClass ( array $arguments = [] ) : object
    {
        if ( $this->getShared )
        {
            return CallClass___::getInstance( $this->className, $arguments );
        }
        
        $instance = new class( $this->className, $arguments )
        {
            use \Red2Horse\Mixins\Traits\TraitCall;

            public function __construct( string $className, array $arguments )
            {
                $this->traitUseBefore = $arguments[ 'traitCallback' ][ 'before' ] ?? false;
                $this->traitUseAfter = $arguments[ 'traitCallback' ][ 'after' ] ?? false;

                $this->run( $className );
            }
        };

        return $instance;
    }
}