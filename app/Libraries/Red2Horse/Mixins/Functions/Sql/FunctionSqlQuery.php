<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Sql;

use Red2Horse\Mixins\Classes\Sql\SqlClassQuery;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function sqlClassQueryInstance ( string $table = 'user', ?string $model = null ) : SqlClassQuery
{
    return SqlClassQuery::setTable( $table, $model );
}

function edit ( array $set, array $where )
{
    return sqlClassQueryInstance()->edit( $set, $where );
}

function delete ( array $where )
{
    return sqlClassQueryInstance()->delete( $where );
}

function add ( array $data )
{
    return sqlClassQueryInstance()->add( $data );
}

function editIn ( array $set, array $where, array $in )
{
    return sqlClassQueryInstance()->editIn( $set, $where, $in );
}

function deleteIn ( array $where, array $in )
{
    return sqlClassQueryInstance()->deleteIn( $where, $in );
}