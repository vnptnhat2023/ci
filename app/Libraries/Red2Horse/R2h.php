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
    functionNamespace,
    initConfig
};

const R2H_BASE_PATH = __DIR__;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

if ( ! $functions_path = realpath( \Red2Horse\R2H_BASE_PATH . '/Mixins/Functions' ) )
{
    throw new \Error( 'Path not found .', 404 );
}

require_once realpath( $functions_path . '/RequireOnce.php' );

/** @todo [ SERIALIZE, INVOKE ] */
class R2h
{
    use TraitSingleton;
    private function __construct ()
	{
        initConfig();
    }

    public function __call ( string $methodName, array $arguments )
    {
        if ( function_exists( $functionName = functionNamespace( $methodName ) ) )
        {
            return call_user_func( $functionName, ...$arguments );
        }
        $setting = [ 'method_name' => $methodName, 'arguments' => $arguments ];
        return callClass( Red2Horse::class, RegistryClass::class, true, $setting );
    }
}