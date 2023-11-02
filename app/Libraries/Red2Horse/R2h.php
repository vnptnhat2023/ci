<?php

declare( strict_types = 1 );
namespace Red2Horse;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;
use Red2Horse\Mixins\Classes\Registry\RegistryClass;
use Red2Horse\Mixins\Classes\Base\Red2Horse;
use Red2Horse\Exception\ErrorPathException;

use function Red2Horse\Mixins\Functions\Instance\callClass;

const R2H_BASE_PATH = __DIR__;

if ( ! $baseHelper = realpath( \Red2Horse\R2H_BASE_PATH . '/BaseHelper.php' ) )
{
    throw new ErrorPathException( sprintf( 'Path not found: %s', $baseHelper ) );
}
require_once( $baseHelper );

helpers( [ 'namespace', 'instance_box', 'instance', 'config', 'sql', 'sql_export', 'model' ] );

/** @todo [ SERIALIZE, INVOKE ] */
class R2h
{
    use TraitSingleton;
    private function __construct () {}

    public function __call ( string $methodName, array $arguments )
    {
        $setting = [ 'method_name' => $methodName, 'arguments' => $arguments ];
        return CallClass( Red2Horse::class, RegistryClass::class, true, $setting );
    }
}