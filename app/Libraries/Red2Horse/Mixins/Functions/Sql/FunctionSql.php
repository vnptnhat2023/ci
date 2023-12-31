<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Sql;

use Red2Horse\Exception\ErrorArrayKeyNotFoundException;
use Red2Horse\Exception\ErrorFileHandleException;
use Red2Horse\Mixins\Classes\Sql\SqlClass;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setErrorWithLang;
use function Red2Horse\Mixins\Functions\Message\setInfoMessage;
use function Red2Horse\Mixins\Functions\Message\setSuccessMessage;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function sqlClassInstance () : SqlClass
{
    return getInstance( SqlClass::class );
}

/** @return mixed */
function getUserField ( string $key, bool $getKey = false )
{
    return getField( $key, 'user', $getKey );
}

/** @return mixed */
function getUserFields ( array $keys )
{
    return getFields( $keys, 'user' );
}

function getUserTableField ( string $key, bool $getKey = false  )
{
    return sqlClassInstance()->getField( $key, 'user', $getKey, true );
}

/** @return mixed */
function getUserGroupFields ( array $keys )
{
    return getFields( $keys, 'user_group' );
}

/** @return mixed */
function getUserGroupField ( string $key, bool $getKey = false )
{
    return getField( $key, 'user_group', $getKey );
}

function getUserGroupTableField ( string $key, bool $getKey = false )
{
    return sqlClassInstance()->getField( $key, 'user_group', $getKey, true );
}

/**
 * @return string|false
 * @throws ErrorArrayKeyNotFoundException
 */
function getTable ( string $key = 'user_group', bool $getKey = false, bool $throw = true, bool $default = false )
{
    return sqlClassInstance()->getTable( $key, $getKey, $throw, $default );
}

function getColumn ( string $key = 'user_group', string $userFunc = '' )
{
    $data = sqlClassInstance()->getColumn( $key );

    if ( function_exists( $userFunc ) )
    {
        $data = call_user_func( $userFunc, $data );
    }

    return $data;
}

function getFields ( array $keys, string $table = 'user_group', bool $columnsFormat = true, $keysFormat = true )
{
    return sqlClassInstance()->getFields( $keys, $table, $columnsFormat, $keysFormat );
}

/** @return mixed */
function getField ( string $key, string $table = 'user_group', bool $getKey = false, bool $getTableKey = false )
{
    return sqlClassInstance()->getField( $key, $table, $getKey, $getTableKey );
}

/** @return mixed */
function getKeyField ( string $key, string $table = 'user_group' )
{
    return sqlClassInstance()->getField( $key, $table, true );
}

/** @return mixed */
function getValueField ( string $key, string $table = 'user_group' )
{
    return sqlClassInstance()->getField( $key, $table );
}

/** @return mixed */
function getValueTableField ( string $key, string $table = 'user_group' )
{
    return sqlClassInstance()->getField( $key, $table, false, true );
}

/** @param null|int|string $port */
function createDatabase (
    string $s,
    string $u,
    string $p,
    string $d,
    $port = null,
    array $intersect = []
) : bool
{
    $configValidation = getConfig( 'validation' );
    $validationComponent = getComponents( 'validation' );

    if ( empty( $intersect ) )
    {
        $intersect = [
            $configValidation->database_hostname,
            $configValidation->database_username,
            $configValidation->database_password,
            $configValidation->database_database,
            $configValidation->database_port
        ];
    }

    $rules = $validationComponent->getRules( $intersect );
    $data = array_combine( $intersect, [ $s, $u, $p, $d, $port ] );
    helpers( [ 'message' ] );

    if ( ! $validationComponent->isValid( $data, $rules ) )
    {
        setErrorMessage( $validationComponent->getErrors() );
        return false;
    }

    if ( ! $conn = databaseConnect( $s, $u, $p, '', ( int ) $port ) )
    {
        return false;
    }

    $common = getComponents( 'common' );
    if ( null === $d )
    {
        setErrorWithLang( 'errorDatabaseNotDefined' );
        return disconnectDatabase( $conn );
    }

    $d = getComponents( 'common' )->esc( $d );
    
    if ( ! chmod(\Red2Horse\R2H_BASE_PATH, 777 ) )
    {
        setErrorWithLang( 'errorFileCannotWrite' );
        return disconnectDatabase( $conn );
    }
    
    $file = \Red2Horse\R2H_BASE_PATH . '/database.php';
    $fp = fopen( $file, 'w' );

    $write = fwrite( $fp, "<?php
\$Red2HorseDatabase = [
    'DSN'      => '',
    'hostname' => '$s',
    'username' => '$u',
    'password' => '$p',
    'database' => '$d',
    'DBDriver' => 'MySQLi',
    'DBPrefix' => '',
    'pConnect' => false,
    'DBDebug'  => (ENVIRONMENT !== 'production'),
    'cacheOn'  => false,
    'cacheDir' => '',
    'charset'  => 'utf8mb4',
    'DBCollat' => 'utf8mb4_general_ci',
    'swapPre'  => '',
    'encrypt'  => false,
    'compress' => false,
    'strictOn' => false,
    'failover' => [],
    'port'     => $port,
];
?>");

    fclose( $fp );

    if ( ! $write )
    {
        throw new ErrorFileHandleException( sprintf( 'Cannot write to file: %s', $file ) );
    }

    if ( ! mysqli_query( $conn, "CREATE DATABASE IF NOT EXISTS {$d}" ) )
    {
        setInfoMessage( $common->lang( 'Red2Horse.errorCreatingDatabase', [ $d ] ) );
        return disconnectDatabase( $conn );
    }

    if ( ! chmod( \Red2Horse\R2H_BASE_PATH, 775 ) )
    {
        throw new ErrorFileHandleException( 'Cannot chmod to 755' );
    }

    setSuccessMessage( '', true );

    return disconnectDatabase( $conn, true );
}

function disconnectDatabase ( \mysqli $conn, bool $return = false ) : bool
{
    mysqli_close( $conn );
    return $return;
}

/** @return false|\mysqli $conn */
function databaseConnect ( string $s, string $u, string $p, string $d, ?int $port = null )
{
    $common = getComponents( 'common' );

    if ( ! $conn = mysqli_connect( $s, $u, $p, $d, $port ) )
    {
        helpers( [ 'message' ] );
        setErrorMessage( $common->lang( 'Red2Horse.errorDatabaseConnect' ) );
        return false;
    }

    return $conn;
}