<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Mixins\Classes\Base\Message;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @todo set: log,throttle */
function getMessageInstance () : Message
{
    return baseInstance( Message::class );
}

/** @param string|array $msgData */
function setErrorMessage ( $msgData ) : void
{
    $msgData = ( array ) $msgData;
    getMessageInstance()::$errors += $msgData;
}

/** @param string|array $msgData */
function setSuccessMessage ( $msgData, bool $withSuccessfully = true ) : void
{
    $msgData = ( array ) $msgData;
    $msg = getMessageInstance();
    $msg::$success += $msgData;

    if ( $withSuccessfully ) $msg::$successfully = true;
}

/** @param string|array $msgData */
function setInfoMessage ( $msgData ) : void
{
    $msgData = ( array ) $msgData;
    getMessageInstance()::$info += $msgData;
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