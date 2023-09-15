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

/** Path: base */
const R2H_BASE_PATH = __DIR__;

/** Paths: configs, functions */
$config_path = realpath( \Red2Horse\R2H_BASE_PATH . '/Config' );
$functions_path = realpath( \Red2Horse\R2H_BASE_PATH . '/Mixins/Functions' );

/** Configs */
require_once realpath( $config_path . '/ConstantNamespace.php' );

/** Functions */
require_once realpath( $functions_path . '/FunctionCall.php' );
require_once realpath( $functions_path . '/FunctionClass.php' );
require_once realpath( $functions_path . '/FunctionNamespace.php' );
require_once realpath( $functions_path . '/FunctionSql.php' );

class R2h
{
    use TraitSingleton;
    public function __construct ( ?\Closure $config = null )
	{
        $config = ( null === $config ) ? getConfig() : $config();

        $configData = [ 'methods' => $config::_getMPs(), 'instance' => $config ];
		setClass( Config::class, $configData, true );

        $throttle = array_values( ( array ) getConfig( 'throttle' )->throttle );
        getComponents( 'throttle' )->config( ...$throttle );
    }

    public function __call ( string $methodName, array $arguments = [] )
    {
        $setting = [
            'method_name' => $methodName,
            'arguments' => $arguments,
            'traitCallback' => [
                'before' => true,
                'after' => false
            ]
        ];

        return callClass( Red2Horse::class, RegistryClass::class, true, $setting );
    }
}