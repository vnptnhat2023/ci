<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Facade\Auth\Message;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function getMessageInstance () : Message
{
    return baseInstance( Message::class );
}

function setErrorMessage ( array $msgData ) : void
{
    getMessageInstance()::$errors += $msgData;
}

function setSuccessMessage ( array $msgData, bool $withSuccessfully = true ) : void
{
    $msg = getMessageInstance();
    $msg::$success += $msgData;

    if ( $withSuccessfully ) $msg::$successfully = true;
}

function setInfoMessage ( array $msgData ) : void
{
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