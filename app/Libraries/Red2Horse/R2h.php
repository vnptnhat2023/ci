<?php

declare( strict_types = 1 );
namespace Red2Horse;

use Red2Horse\
{
    Mixins\Traits\Object\TraitSingleton,
    Mixins\Classes\Registry\RegistryClass,
    Mixins\Classes\Base\Red2Horse
};

use function Red2Horse\Mixins\Functions\Config\initConfig;
use function Red2Horse\Mixins\Functions\Instance\callClass;

const R2H_BASE_PATH = __DIR__;

/**
 * @param array $name
 * @param null|string $add Add a path file
 */
function helper ( array $names, ?string $add = null ) : void
{
    if ( ! $functionPath = realpath( \Red2Horse\R2H_BASE_PATH . '/Mixins/Functions' ) )
    {
        throw new \Error( sprintf( 'Path not found: %s', $functionPath ), 404 );
    }

    static $functionNames = [
        'constant'          => \Red2Horse\R2H_BASE_PATH . '/Config/ConstantNamespace.php',
        'event'             => '/Event/FunctionEvent.php',
        'instance_box'      => '/Instance/FunctionInstanceBox.php',
        'instance'          => '/Instance/FunctionInstance.php',
        'message'           => '/Message/FunctionMessage.php',
        'namespace'         => '/NS/FunctionNS.php',
        'config'            => '/Config/FunctionConfig.php',
        'authorization'     => '/Auth/FunctionAuthorization.php',
        'password'          => '/Password/FunctionPassword.php',
        'array'             => '/Data/FunctionsArrays.php',
        'sql'               => '/Sql/FunctionSql.php',
        'sql_export'        => '/Sql/FunctionSqlExport.php',
        'query'             => '/Sql/FunctionSqlQuery.php'
    ];

    static $required = [];

    if ( ! in_array( $add, $functionNames ) )
    {
        $functionNames[] = $add;
    }

    if ( [] === ( $diffs = array_diff( $names, $required ) ) )
    {
        return;
    }

    foreach ( $diffs as $diff )
    {
        if ( $requireStr = realpath( $functionPath . $functionNames[ $diff ] ) )
        {
            $required[] = $required;
            require_once $requireStr;
        }
    }
}

\Red2Horse\helper( [ 'constant', 'event', 'instance_box', 'instance', 'message',
    'namespace', 'config', 'sql', 'sql_export', 'authorization', 'password', 'array', 'query'
] );

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
        // if ( function_exists( $functionName = functionNamespace( $methodName ) ) )
        // {
        //     return call_user_func( $functionName, ...$arguments );
        // }
        $setting = [ 'method_name' => $methodName, 'arguments' => $arguments ];
        return CallClass( Red2Horse::class, RegistryClass::class, true, $setting );
    }
}