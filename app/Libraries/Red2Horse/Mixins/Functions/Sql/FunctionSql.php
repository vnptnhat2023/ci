<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Sql;

use Red2Horse\Mixins\Classes\Sql\SqlClass;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
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

function getTable ( string $key = 'user_group', bool $getKey = false ) : string
{
    return sqlClassInstance()->getTable( $key, $getKey );
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
function getField ( string $key, string $table = 'user_group', bool $getKey = false )
{
    return sqlClassInstance()->getField( $key, $table, $getKey );
}

function createDatabase ( $s, $u, $p, $d, $port = null, array $intersect = [] ) : bool
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

    if ( ! $validationComponent->isValid( $data, $rules ) )
    {
        setErrorMessage( $validationComponent->getErrors() );
        return false;
    }

    // dd( $s, $u, $p, $d, ( int ) $port );
    if ( ! $conn = databaseConnect( $s, $u, $p, '', ( int ) $port ) )
    {
        return false;
    }

    $common = getComponents( 'common' );
    if ( null === $d )
    {
        setErrorMessage( $common->lang( 'Red2Horse.errorDatabaseNotDefined' ) );
        return disconnectDatabase( $conn );
    }

    $d = getComponents( 'common' )->esc( $d );
    
    
    if ( ! chmod(\Red2Horse\R2H_BASE_PATH, 777 ) )
    {
        setErrorMessage( $common->lang( 'Cannot chmod file' ) );
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
        throw new \Error( 'Not Acceptable', 406 );
    }

    if ( ! mysqli_query( $conn, "CREATE DATABASE IF NOT EXISTS {$d}" ) )
    {
        setInfoMessage( $common->lang( 'Red2Horse.errorCreatingDatabase', [ $d ] ) );
        return disconnectDatabase( $conn );
    }

    if ( ! chmod( \Red2Horse\R2H_BASE_PATH, 775 ) )
    {
        throw new \Error( 'Cannot chmod to 755', 406 );
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
        setErrorMessage( $common->lang( 'Red2Horse.errorDatabaseConnect' ) );
        return false;
    }

    return $conn;
}