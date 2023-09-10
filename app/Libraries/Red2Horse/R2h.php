<?php

declare( strict_types = 1 );
namespace Red2Horse;

use Red2Horse\
{
    Mixins\Traits\TraitSingleton,
    Facade\Auth\Config,
    Facade\Auth\Red2Horse
};
use Red2Horse\Mixins\Classes\Registry\RegistryClass;

use function Red2Horse\Mixins\Functions\
{
    callClass, getComponents, setClass
};

define( 'R2H_PATH', __DIR__ );

$functions_path = realpath( R2H_PATH . '/Mixins/Functions' );
require_once( $functions_path . '/FunctionCall.php' );
require_once( $functions_path . '/FunctionClass.php' );

class R2h
{
    use TraitSingleton;

    public function __construct ( ?Config $config = null )
	{
        $config = $config ?? Config::getInstance();
        $configData = [ 'methods' => $config::_getMPs(), 'instance' => $config ];
		setClass( Config::class, $configData, true );

        $throttle = array_values( ( array ) $config->throttle );
        getComponents( 'throttle' )->config( ...$throttle );
    }

    public function __call ( string $name, array $arguments = [] )
    {
        $setting = [ 'traitCallback' => [ 'before' => true, 'after' => true ] ];
        return callClass( Red2Horse::class, RegistryClass::class, true, $setting ) ->__call( $name, $arguments );
    }
}