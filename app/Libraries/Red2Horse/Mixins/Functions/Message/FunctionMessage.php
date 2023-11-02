<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Message;

use Red2Horse\Mixins\Classes\Base\Message;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Throttle\throttleIncrement;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsSupported;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @todo set: log,throttle */
function getMessageInstance () : Message
{
    return getBaseInstance( Message::class );
}

function messenger( array $add = [], bool $asObject = false, bool $getConfig = false ) : array
{
    return getMessageInstance()->getMessage( $add, $asObject, $getConfig );
}

/** @param string|array $msgData */
function setErrorMessage ( $msgData, bool $throttle = false, ?\Closure $callable = null, ...$args ) : void
{
    $msgData        = ( array ) $msgData;
    $msg            = getMessageInstance();
    $msg::$errors   = array_merge( $msg::$errors, $msgData );

    if (  $throttle )
    {
        helpers( [ 'throttle' ] );

        if ( throttleIsSupported() )
        {
            throttleIncrement();
        }
    }

    if ( is_callable( $callable ) )
    {
        $callable( ...$args );
    }
}

/** @param string|array $msgData */
function setSuccessMessage ( $msgData = [], bool $withSuccessfully = true, ?\Closure $callable = null, ...$args ) : void
{
    $msg = getMessageInstance();
    if ( ! empty( $msgData ) )
    {
        $msgData = ( array ) $msgData;
        $msg::$success = array_merge( $msg::$success, $msgData );
    }

    if ( $withSuccessfully ) $msg::$successfully = true;

    if ( is_callable( $callable ) )
    {
        $callable( ...$args );
    }
}

/** @param string|array $msgData */
function setInfoMessage ( $msgData, ?\Closure $callable = null, ...$args ) : void
{
    $msgData = ( array ) $msgData;
    $msg        = getMessageInstance();
    $msg::$info = array_merge( $msg::$info, $msgData );

    if ( is_callable( $callable ) )
    {
        $callable( ...$args );
    }
}

function getErrorMessage ( ?string $key = null )
{
    $msg = getMessageInstance()::$errors;
    
    return ( null === $key ) 
        ? $msg 
        : $msg[ $key ] ?? $msg;
}

function getSuccessMessage ( ?string $key = null )
{
    $msg = getMessageInstance()::$success;
    
    return ( null === $key ) 
        ? $msg 
        : $msg[ $key ] ?? $msg;
}

function getInfoMessage ( ?string $key = null )
{
    $msg = getMessageInstance()::$info;
    
    return ( null === $key ) 
        ? $msg 
        : $msg[ $key ] ?? $msg;
}