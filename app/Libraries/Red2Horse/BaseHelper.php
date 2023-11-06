<?php

declare( strict_types = 1 );
namespace Red2Horse;

use Red2Horse\Exception\ErrorArgumentException;
use Red2Horse\Exception\ErrorPathException;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @param array|\stdClass|string $helperName */
function helpers ( $helperName, array $add = [] ) : void
{
    if ( ! $functionPath = realpath( \Red2Horse\R2H_BASE_PATH . '/Mixins/Functions' ) )
    {
        throw new ErrorPathException( sprintf( 'Path not found: %s', $functionPath ) );
    }

    static $functionNames = [
        'event'             => '/Event/FunctionEvent.php',
        'instance_box'      => '/Instance/FunctionInstanceBox.php',
        'instance'          => '/Instance/FunctionInstance.php',
        'message'           => '/Message/FunctionMessage.php',
        'namespace'         => '/NS/FunctionNS.php',
        'config'            => '/Config/FunctionConfig.php',
        'authorization'     => '/Auth/FunctionAuthorization.php',
        'password'          => '/Password/FunctionPassword.php',
        'array_data'        => '/Data/FunctionsArrays.php',
        'sql'               => '/Sql/FunctionSql.php',
        'sql_export'        => '/Sql/FunctionSqlExport.php',
        'model'             => '/Model/FunctionModel.php',
        'throttle'          => '/Throttle/FunctionThrottle.php',
        'common'            => '/Common/FunctionCommon.php'
    ];

    if ( [] !== $add )
    {
        $isAssoc = array_keys( $add ) !== range( 0, count( $add ) - 1 );
        if ( ! $isAssoc )
        {
            $errorArgument = sprintf( 'Argument 1: "add" must be an array associative' );
            throw new ErrorArgumentException( $errorArgument );
        }

        $functionNames = array_merge( $functionNames, $add );
    }

    static $required = [];

    if ( is_string( $helperName ) || $helperName instanceof \stdClass ) 
    {
        $helperName = ( array ) $helperName;
    }

    foreach ( $helperName as $name )
    {
        $path = $functionPath . $functionNames[ $name ];

        if ( in_array( $name, $required, true ) )
        {
            continue;
        }

        if ( $requireStr = realpath( $path ) )
        {
            $required[] = $required;
            require_once $requireStr;
        }
        else
        {
            throw new ErrorPathException( sprintf( 'Path not found: %s', $path ) );
        }
    }
}