<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Facade\Auth\Message;
use Red2Horse\Mixins\Classes\SqlClass;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function sqlClassInstance () : SqlClass
{
    return getInstance( SqlClass::class );
}

/** @return mixed */
function getUserField ( string $key )
{
    return getField( $key, 'user' );
}

/** @return mixed */
function getUserGroupField ( string $key )
{
    return getField( $key, 'user_group' );
}

function getTable ( string $key = 'user_group', bool $getKey = false ) : string
{
    return sqlClassInstance()->getTable( $key, $getKey );
}

function getColumn ( string $key = 'user_group' )
{
    return sqlClassInstance()->getColumn( $key );
}

function getFields ( array $keys, string $table = 'user_group' )
{
    return sqlClassInstance()->getFields( $keys, $table );
}

/** @return mixed */
function getField ( string $key, string $table = 'user_group' )
{
    return sqlClassInstance()->getField( $key, $table );
}

function createDatabase ( string $s, string $u, string $p, string $d, ?int $port = null ) : bool
{
    $message = getInstance( Message::class );
    $common = getComponents( 'common' );

    if ( ! $conn = mysqli_connect( $s, $u, $p, null, $port ) )
    {
        $message::$error[] = $common->lang( 'Red2Horse.errorDatabaseConnect' );
        return false;
    }

    if ( null === $d )
    {
        $message::$error[] = $common->lang( 'Red2Horse.errorDatabaseNotDefined' );
        return false;
    }

    if ( ! mysqli_query( $conn, "CREATE DATABASE IF NOT EXISTS {$d}" ) )
    {
        $message::$info[] = $common->lang( 'Red2Horse.errorCreatingDatabase', [ $d ] );
        return false;
    }
    
    $message::$successfully = true;
    $message::$success[] = $common->lang( 'Red2Horse.successCreatedDatabase', [ $d ] );

    mysqli_close( $conn );
    return true;
}