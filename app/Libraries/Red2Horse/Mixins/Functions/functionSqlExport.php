<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Mixins\Classes\SqlClassExport;
use function Red2Horse\Mixins\Functions\getInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function sqlClassExportInstance() : SqlClassExport
{
    return getInstance( SqlClassExport::class );
}

function sqlSelectColumn ( array $userColumns, bool $join = true ) : string
{
    return sqlClassExportInstance()->sqlSelectColumn( $userColumns, $join );
}

function sqlSelectColumns ( array $addColumns = [], bool $join = true ) : string
{
    $str = sqlClassExportInstance()->sqlSelectColumns( $addColumns, $join );
    return $str;
}

function createTable ( string $table, bool $query = false ) : string
{
    return sqlClassExportInstance()->createTable( $table, $query );
}

function seed ( string $table, array $intersect = [], bool $query = false ) : array
{
    if ( empty( $intersect ) || $table == 'user' || $table == 'user_group' )
    {
        if ( $table == 'user' )
        {
            $intersect = [
                getUserField( 'username' ),
                getUserField( 'password' ),
                getUserField( 'email' )
            ];
        }
        else
        {
            $intersect = [
                getUserGroupField( 'name' )[ 0 ],
                getUserGroupField( 'permission' ),
                getUserGroupField( 'role' )
            ];
        }
    }

    return sqlClassExportInstance()->seed( $table, $intersect, $query );
}