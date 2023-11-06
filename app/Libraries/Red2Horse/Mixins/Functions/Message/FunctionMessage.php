<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Message;

use Red2Horse\Mixins\Classes\Base\Message;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Common\lang;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
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

/** @param string|array $text */
function setErrorMessage ( $text, bool $throttle = false, ?\Closure $callable = null, ...$args ) : void
{
    getMessageInstance()->setErrors( ( array ) $text );

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

function setErrorWithLang ( string $line = '', array $args = [], bool $throttle = false, string $linePrefix = 'Red2Horse.' ) : void
{
    helpers( 'common' );

    $message = lang( sprintf( '%s.%s', $linePrefix, $line ), $args );
    getMessageInstance()->setErrors( [ $message ] );

    if ( $throttle )
    {
        helpers( 'throttle' );
        if ( throttleIsSupported() )
        {
            throttleIncrement();
        }
    }
}

/** @param array|string|\stdClass $text */
function setSuccessMessage ( $text, bool $withSuccessfully = true, ?\Closure $callable = null, ...$args ) : void
{
    $instance = getMessageInstance();
    $instance->setSuccess( $text );

    if ( $withSuccessfully )
    {
        $instance->setSuccessfully( true );
    }

    if ( is_callable( $callable ) )
    {
        $callable( ...$args );
    }
}

function setSuccessWithLang ( bool $withSuccess = true, string $line = '', array $args = [], string $linePrefix = 'Red2Horse.' ) : void
{
    helpers( 'common' );
    $instance = getMessageInstance();

    if ( $withSuccess )
    {
        $instance->setSuccessfully( true );
    }

    $message = lang( sprintf( '%s.%s', $linePrefix, $line ), $args );
    $instance->setSuccess( $message );
}

/** @param string|array $text */
function setInfoMessage ( $text, ?\Closure $callable = null, ...$args ) : void
{
    getMessageInstance()->setInfo( ( array ) $text );

    if ( is_callable( $callable ) )
    {
        $callable( ...$args );
    }
}

function setInfoWithLang ( string $line = '', array $args = [], string $linePrefix = 'Red2Horse.' ) : void
{
    helpers( 'common' );
    $instance = getMessageInstance();

    $message = lang( sprintf( '%s.%s', $linePrefix, $line ), $args );
    $instance->setInfo( $message );
}

/** @return mixed */
function getErrorMessage ( ?string $key = null )
{
    $errors = getMessageInstance()->getErrors();

    if ( null === $key )
    {
        return $errors;
    }

    return $errors[ $key ] ?? $errors;
}

/** @return mixed */
function getSuccessMessage ( ?string $key = null )
{
    $success = getMessageInstance()->getSuccess();

    if ( null === $key )
    {
        return $success;
    }

    return $success[ $key ] ?? $success;
}

/** @return mixed */
function getInfoMessage ( ?string $key = null )
{
    $info = getMessageInstance()->getInfo();

    if ( null === $key )
    {
        return $info;
    }

    return $info[ $key ] ?? $info;
}