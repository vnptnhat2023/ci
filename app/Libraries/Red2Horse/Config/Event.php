<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

// use Red2Horse\Facade\Auth\Event as AuthEvent;
use Red2Horse\Mixins\Functions\UserDefinedFunctions;
use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Event
{
    use TraitSingleton;

    public bool $manyTrigger = false;
    public array $events = [
        'r2h_before_get_message'        => UserDefinedFunctions::class,
        'r2h_after_get_message'         => UserDefinedFunctions::class,
        'r2h_before_is_logged'          => UserDefinedFunctions::class,
        'r2h_after_is_logged'           => UserDefinedFunctions::class,
        'r2h_before_get_result'         => UserDefinedFunctions::class,
        'r2h_after_get_result'          => UserDefinedFunctions::class,
        'r2h_before_login'              => UserDefinedFunctions::class,
        'r2h_after_login'               => UserDefinedFunctions::class,
        'r2h_before_logout'             => UserDefinedFunctions::class,
        'r2h_after_logout'              => UserDefinedFunctions::class,
        'r2h_before_request_password'   => UserDefinedFunctions::class,
        'r2h_after_request_password'    => UserDefinedFunctions::class
    ];
    public bool $useBefore = true;
    public bool $useAfter = true;
    public string $beforePrefix = 'R2h_before_';
    public string $afterPrefix = 'R2h_after_';

    private function __construct () {}
}