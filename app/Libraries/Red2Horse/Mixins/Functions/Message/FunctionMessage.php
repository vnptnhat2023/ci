<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Message;

use Red2Horse\Mixins\Classes\Base\Message;

use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @todo set: log,throttle */
function getMessageInstance () : Message
{
    return getBaseInstance( Message::class );
}

/** @param string|array $msgData */
function setErrorMessage ( $msgData ) : void
{
    $msgData        = ( array ) $msgData;
    $msg            = getMessageInstance();
    $msg::$errors   = array_merge( $msg::$errors, $msgData );
}

/** @param string|array $msgData */
function setSuccessMessage ( $msgData = [], bool $withSuccessfully = true ) : void
{
    $msg = getMessageInstance();
    if ( ! empty( $msgData ) )
    {
        $msgData = ( array ) $msgData;
        $msg::$success = array_merge( $msg::$success, $msgData );
    }

    if ( $withSuccessfully ) $msg::$successfully = true;
}

/** @param string|array $msgData */
function setInfoMessage ( $msgData ) : void
{
    $msgData = ( array ) $msgData;
    $msg        = getMessageInstance();
    $msg::$info = array_merge( $msg::$info, $msgData );
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