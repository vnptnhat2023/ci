<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ConstantNamespace
{
    use TraitSingleton;

    private const BASE_NAMESPACE    = 'Red2Horse\\';
    private const ADAPTER_NAME      = 'Codeigniter\\';

    public const LIST_NAMESPACE = [
        'CONFIG_NAMESPACE'          => self::BASE_NAMESPACE . 'Config\\',
        'FACADE_NAMESPACE'          => self::BASE_NAMESPACE . 'Facade\\',
        'ADAPTER_NAMESPACE'         => self::BASE_NAMESPACE . 'Adapter\\' . self::ADAPTER_NAME,
        'FUNCTION_NAMESPACE'        => self::BASE_NAMESPACE . 'Mixins\\Functions\\',
        'REGISTRY_NAMESPACE'        => self::BASE_NAMESPACE . 'Mixins\\Classes\\Registry\\',
        'BASE_NAMESPACE'            => self::BASE_NAMESPACE . 'Mixins\\Classes\\Base\\',
        'EXCEPTION_NAMESPACE'       => self::BASE_NAMESPACE . 'Exception\\',
        /** Other */
        'MODEL_NAMESPACE'           => self::BASE_NAMESPACE . 'Model\\',
    ];

    private function __construct () {}
}