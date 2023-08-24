<?php
declare( strict_types = 1 );
namespace Red2Horse;

use Red2Horse\{
    Mixins\TraitCall,
    Facade\Auth\Config,
    Facade\Auth\Red2Horse
};

/**
 * @method __construct
 * @method login
 * @method logout
 * @method requestPassword
 * @method getUserdata
 * @method getHashPass
 * @method getVerifyPass
 * @method getResult
 * @method getMessage
 * @method withPermission
 * @method withGroup
 * @method withRole
 * @method isLogged
 * @method regenerateSession
 * @method regenerateCookie
 * @method getInstance
 * @method getMethods
 * @method __clone
 * @method __debugInfo
 * @method __debugBacktrace
 */
class R2h
{
    use TraitCall;

    public function __construct ( ?Config $config = null )
	{
        $this->traitCallInstance = Red2Horse::getInstance( $config );
        $this->traitCallMethods = Red2Horse::getMethods();

        $callback = function( string $name, array $args ) {
            return $this->traitCallInstance->event->trigger( $name, $args );
        };

        $this->traitCallback[ 'callback' ] = $callback;
        $this->traitCallback[ 'before' ] = true;
    }
}