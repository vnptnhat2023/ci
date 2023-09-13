<?php

declare( strict_types = 1 );
namespace Red2Horse;

use Red2Horse\
{
    Mixins\Traits\TraitSingleton,
    Mixins\Classes\Registry\RegistryClass,
    Facade\Auth\Red2Horse
};

use function Red2Horse\Mixins\Functions\
{
    callClass,
    getComponents,
    getConfig,
    setClass
};

define( 'R2H_PATH', __DIR__ );

$config_path = realpath( R2H_PATH . '/Config' );
$functions_path = realpath( R2H_PATH . '/Mixins/Functions' );

require_once( $config_path . '/Constants.php' );
require_once( $functions_path . '/FunctionCall.php' );
require_once( $functions_path . '/FunctionClass.php' );


class R2h
{
    use TraitSingleton;
    public function __construct ( ?\Closure $config = null )
	{
        $config = null === $config ? getConfig() : $config();

		setClass( Config::class, [ 'methods' => $config::_getMPs(), 'instance' => $config ], true );
        $throttle = array_values( ( array ) getConfig( 'throttle' )->throttle );
        getComponents( 'throttle' )->config( ...$throttle );
    }

    public function __call ( string $methodName, array $arguments = [] )
    {
        $setting = [
            'traitCallback' => [
                'before' => true,
                'after' => false
            ]
        ];

        return callClass( Red2Horse::class, RegistryClass::class, true, $setting )
            ->__call( $methodName, $arguments );
    }
}