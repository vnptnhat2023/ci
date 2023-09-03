<?php

declare( strict_types = 1 );
namespace Red2Horse;

use Red2Horse\{
    Mixins\TraitCall,
    Mixins\TraitSingleton,

    Facade\Auth\Config,
    Facade\Auth\Red2Horse
};

use function Red2Horse\Mixins\Functions\
{
    getComponents, setClass
};

define( 'R2H_PATH', __DIR__ );

$functions_path = realpath( R2H_PATH . '/Mixins/Functions' );
require_once( $functions_path . '/Functions.php' );

class R2h
{
    use TraitCall, TraitSingleton;

    public function __construct ( ?Config $config = null )
	{
        $config = $config ?? Config::getInstance();
		setClass
        (
            Config::class,
            [ 'methods' => $config::_getMPs(), 'instance' => $config ],
            true
        );

        $throttle = $config->throttle;
        getComponents( 'throttle' )->config(
			$throttle->type,
			$throttle->captchaAttempts,
			$throttle->maxAttempts,
			$throttle->timeoutAttempts
		);

        Red2Horse::getInstance( $config );
        $this->run( Red2Horse::class );
    }
}