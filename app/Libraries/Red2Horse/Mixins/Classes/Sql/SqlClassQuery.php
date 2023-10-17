<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql;

use Red2Horse\Mixins\
{
    Traits\Object\TraitSingleton,
    Interfaces\Sql\SqlClassQueryInterface
};
use Red2Horse\Mixins\Classes\Data\DataAssocKeyMap;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Sql\getTable;
use function Red2Horse\Mixins\Functions\Sql\keyValueExports;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlClassQuery implements SqlClassQueryInterface
{
    use TraitSingleton;

    public string $table;

    protected $model;

    public static function setTable ( string $table, ?string $model = null ) : self
    {        
        return getInstance( self::class )->table( $table, $model );
    }

    public function table ( string $table, ?string $model = null ) : self
    {
        $this->table = getTable( $table );
        $this->model ??= getComponents( 'query' );
        return $this;
    }

    public function query ( string $sql )
    {
        if ( getConfig( 'sql' )->useQuery )
        {
            return $this->model->querySimple( $sql );
        }

        return $sql;
    }

    public function edit ( array $set, array $where )
    {
        $template = getConfig( 'sql' )->updateTemplate;
        $set = keyValueExports( $set );
		$where = keyValueExports( $where );

        $sql = sprintf( $template, $this->table, $set, $where );

        return $this->query( $sql );
    }

    public function delete ( array $where )
    {
        $template = getConfig( 'sql' )->deleteTemplate;
        $sql = sprintf( $template, $this->table, keyValueExports( $where ) );

        return $this->query( $sql );
    }

    public function add ( array $data )
    {
        $arrayAssocKeyMap = new DataAssocKeyMap;
        $data = $arrayAssocKeyMap( $data, false );
        $columns = implode( ',', array_keys( $data ) );
        $values = implode( ',', array_values( $data ) );

        $template = getConfig( 'sql' )->insertTemplate;
        $sql = sprintf( $template, $this->table, $columns, $values );

        return $this->query( $sql );
    }

    public function editIn ( array $set, array $where, array $in )
    {
        $template = getConfig( 'sql' )->updateInTemplate;
        $set = keyValueExports( $set );
        $where = keyValueExports( $where );

        $inMap = fn( $in ) => getComponents( 'common' )->esc( ( string ) $in );
        $in = implode( ',', array_map( $inMap, $in ) );

        $sql = sprintf( $template, $this->table, $set, $where, $in );

        return $this->query( $sql );
    }

    public function deleteIn ( array $where, array $in )
    {
        $template = getConfig( 'sql' )->deleteInTemplate;
        $where = keyValueExports( $where );

        $inMap = fn( $in ) => getComponents( 'common' )->esc( ( string ) $in );
        $in = implode( ',', array_map( $inMap, $in ) );

        $sql = sprintf( $template, $this->table, $where, $in );

        return $this->query( $sql );
    }
}