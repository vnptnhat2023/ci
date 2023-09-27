<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions;

use Red2Horse\Mixins\Classes\SqlClassExport;

use function Red2Horse\Mixins\Functions\getInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function sqlExportInstance() : SqlClassExport
{
    return getInstance( SqlClassExport::class );
}

function sqlGetColumn ( array $userColumns, bool $join = true ) : string
{
    return sqlExportInstance()->sqlGetColumn( $userColumns, $join );
}

function sqlGetColumns ( array $addColumns = [], bool $join = true ) : string
{
    return sqlExportInstance()->sqlGetColumns( $addColumns, $join );
}

function importUserGroup () : string
{
    return sqlExportInstance()->importUserGroup();
}

function importUser () : string
{
    return sqlExportInstance()->importUser();
}

function seedUser ( array $intersect = [] ) : array
{
    $table = getTable( 'user' );
    $intersect = empty( $intersect ) 
        ? [
            getUserField( 'username' ),
            getUserField( 'password' ),
            getUserField( 'email' )
        ]
        : $intersect;

    return sqlExportInstance()->requestToSql( $table, $intersect );
}

function seedUserGroup ( array $intersect = [] ) : array
{
    $table = getTable( 'user_group' );
    $intersect = empty( $intersect ) 
        ? [
            getUserGroupField( 'name' )[ 0 ],
            getUserGroupField( 'permission' ),
            getUserGroupField( 'role' )
        ]
        : $intersect;

    return sqlExportInstance()->requestToSql( $table, $intersect );
}