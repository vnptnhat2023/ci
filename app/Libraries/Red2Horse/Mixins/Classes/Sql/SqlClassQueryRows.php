<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql;

use \Closure;

use Red2Horse\Exception\
{
    ErrorPropertyException,
    ErrorArrayException,
    ErrorParameterException
};

use Red2Horse\Mixins\Interfaces\Sql\SqlClassQueryRowsInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Data\
{
    dataKeyAssocInstance,
    dataKeyInstance
};
use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlClassQueryRows implements SqlClassQueryRowsInterface
{
    use TraitSingleton;

    public string $table = 'table';

    public int $updateLimitRows = 1000;
    public int $deleteLimitRows = 1000;
    public int $getLimitRows = 1000;

    private array $select = [];
    private array $distinct = [];
    private array $update = [];
    private array $delete = [];
    private array $insert = [];
    private array $from = [];
    private array $andWhere = [];
    private array $orWhere = [];
    private array $where = [];
    private array $join = [];
    private array $set = [];
    private array $limit = [];
    private array $andOn = [];
    private array $orOn = [];
    private array $on = [];
    private array $in = [];
    private array $orderBy = [];
    private array $get = [];

    private array $lastQueryString = [];

    private function __construct () {}

    public function getLastQueryString () : string
    {
        if ( ! empty( $this->lastQueryString ) )
        {
            return $this->lastQueryString[ array_key_last( $this->lastQueryString )];
        }

        return '';
    }

    public function select ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $dataNonAssoc->isSelect = true;
        $this->_afterCall( $callable, $dataNonAssoc );

        $this->select[] = $dataNonAssoc->keyMap( $data );
        return $this;
    }

    public function distinct ( array $data, ?Closure $callable = null, int $len = 100  ) : self
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $dataNonAssoc->isSelect = true;
        $this->_afterCall( $callable, $dataNonAssoc );

        $this->distinct[] = $dataNonAssoc->keyMap( $data );
        return $this;
    }

    public function get () : self
    {
        $isEmptyData = empty( $this->select ) && empty( $this->distinct ) && empty( $this->from );

        if ( $isEmptyData )
        {
            throw new ErrorPropertyException;
        }

        $join   = $this->_compileJoin();
        $on     = $this->_compileOn( false );
        $where  = $this->_compileWhere( false );#dd( $where );
        $in     = $this->_compileIn();
        $order  = $this->_compileOrderBy();
        $limit  = $this->_compileLimit();

        $sql = sprintf( 'SELECT %s FROM %s', $this->_compileSelect(), $this->_compileFrom() );

        '' === $join    || $sql .= sprintf( ' JOIN %s', $join );
        '' === $on      || $sql .= sprintf( ' ON %s ', $on );
        '' === $where   || $sql .= sprintf( ' WHERE %s', $where );
        '' === $in      || $sql .= sprintf( ' IN (%s)', $in );
        '' === $order   || $sql .= sprintf( ' ORDER_BY %s', $order );
        '' === $limit   || $sql .= sprintf( ' LIMIT %s', $limit );

        $this->get[] = $sql;
        $this->lastQueryString[] = $sql;

        return $this;
    }

    private function _compile ( string $propName, $throw = true ) : string
    {
        $orProp = 'or' . ucfirst( $propName );
        $andProp = 'and' . ucfirst( $propName );
        $data = $this->{ $propName };
        $orData = $this->{ $orProp };
        $andData = $this->{ $andProp };

        $isEmptyData = empty( $data ) && empty( $orData ) && empty( $andData );

        if ( $isEmptyData && $throw )
        {
            throw new ErrorArrayException;
        }

        $emptyData     = empty( $data );
        $emptyOrData   = empty( $orData );
        $emptyAndData  = empty( $andData );
        $sql           = '';

        if ( ! $emptyData )
        {
            $sql .= implode( $data );
        }

        if ( ! $emptyOrData )
        {
            $prefix = $emptyData && $emptyAndData ? '' : 'OR';
            $sql .= sprintf( ' %s %s', $prefix, implode( $orData ) );
        }

        if ( ! $emptyAndData )
        {
            $prefix = $emptyData && $emptyOrData ? '' : 'AND';
            $sql .= sprintf( ' %s %s', $prefix, implode( $andData ) );
        }

        return $sql;
    }
    
    private function _compileWhere ( $throw = true ) : string
    {
        return $this->_compile( 'where', $throw );
    }

    private function _compileOn ( $throw = true ) : string
    {
        return $this->_compile( 'on', $throw );
    }

    private function _compileOtherProps ( string $propName, string $implodeChar = ', ' ) : string
    {
        $str = '';
        if ( ! empty( $this->$propName ) )
        {
            $str = implode( "{$implodeChar} ", $this->$propName );
        }
        return $str;
    }

    private function _compileFrom ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'from', $implodeChar );
    }

    private function _compileJoin ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'join', $implodeChar );
    }

    private function _compileLimit ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'limit', $implodeChar );
    }

    private function _compileIn ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'in', $implodeChar );
    }

    private function _compileSelect ( string $implodeChar = ', ') : string
    {
        // $select = $this->_compileOtherProps( 'select', $implodeChar );

        // if ( '' !== $distinct = $this->_compileDistinct() )
        // {

        // } 
        return $this->_compileOtherProps( 'select', $implodeChar );
    }

    private function _compileDistinct ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'distinct', $implodeChar );
    }

    private function _compileOrderBy ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'orderBy', $implodeChar );
    }

    public function update ( int $len = 1, ?Closure $callable = null ) : self
    {
        if ( empty( $setData = $this->set ) )
        {
            throw new ErrorArrayException;
        }

        if ( $len > $this->updateLimitRows )
        {
            throw new \Error( sprintf( 'Argument "$len" %s > %s', $len, $this->updateLimitRows ) );
        }

        $whereSql = $this->_compileWhere();

        /** SET sql string */
        $setSql    = implode( ', ', $setData );

        $isIn       = ! empty( $this->in );
        $isJoin     = ! empty( $this->join );
        $isJoinIn   = $isIn && $isJoin;

        /** Template config */
        $sqlConfig  = getConfig( 'sql' );

        if ( $isJoinIn )
        {
            $joinInTemplate = $sqlConfig->updateJoinInTemplate;
            $joinSql = implode( ', ', $this->join );
            // add more ON
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', trim( $onSql ) );
            }
            $inSql = implode( ', ', $this->in );

            $sql = sprintf( $joinInTemplate, $this->table, $joinSql, $setSql, $whereSql, $inSql );
        }
        else if ( $isJoin )
        {
            $joinTemplate = $sqlConfig->updateJoinTemplate;
            $joinSql = implode( ', ', $this->join );
            // add more ON
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', trim( $onSql ) );
            }

            $sql = sprintf( $joinTemplate, $this->table, $joinSql, $setSql, $whereSql );
        }
        else if ( $isIn )
        {
            $inTemplate = $sqlConfig->updateInTemplate;
            $inSql = implode( ', ', $this->in );

            $sql = sprintf( $inTemplate, $this->table, $setSql, $whereSql, $inSql );
        }
        else
        {
            $updateTemplate = $sqlConfig->updateTemplate;

            $sql = sprintf( $updateTemplate, $this->table, $setSql, $whereSql );
        }

        $sql .= " LIMIT $len";

        $this->update[] = $sql;
        $this->lastQueryString[] = $sql;
        return $this;
    }

    public function delete ( $len = 1, ?Closure $callable = null ) : self
    {
        if ( $len > $this->deleteLimitRows )
        {
            $error = sprintf( 'Argument "$len" %s > %s', $len, $this->deleteLimitRows );
            throw new ErrorParameterException( $error );
        }

        $whereSql = $this->_compileWhere();

        $isIn       = ! empty( $this->in );
        $isJoin     = ! empty( $this->join );
        $isJoinIn   = $isIn && $isJoin;

        /** Template config */
        $sqlConfig  = getConfig( 'sql' );

        if ( $isJoinIn )
        {
            $joinInTemplate = $sqlConfig->deleteJoinInTemplate;
            $joinSql = implode( ', ', $this->join );
            // add more ON
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', $onSql );
            }

            $inSql = implode( ', ', $this->in );

            $sql = sprintf( $joinInTemplate, $joinSql, $this->table, $whereSql, $inSql );
        }
        else if ( $isJoin )
        {
            $joinTemplate = $sqlConfig->deleteJoinTemplate;
            $joinSql = implode( ', ', $this->join );
            // add more ON
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', $onSql );
            }

            $sql = sprintf( $joinTemplate, $joinSql, $this->table, $whereSql );
        }
        else if ( $isIn )
        {
            $inTemplate = $sqlConfig->deleteInTemplate;
            $inSql = implode( ', ', $this->in );

            $sql = sprintf( $inTemplate, $this->table, $whereSql, $inSql );
        }
        else
        {
            $deleteTemplate = $sqlConfig->deleteTemplate;

            $sql = sprintf( $deleteTemplate, $this->table, $whereSql );
        }

        $sql .= " LIMIT $len";

        $this->delete[] = $sql;
        $this->lastQueryString[] = $sql;
        return $this;
    }

    /** @TODO ON DUPLICATE KEY UPDATE */
    public function insert ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyAssocInstance();
        $this->_afterCall( $callable, $dataNonAssoc );

        $data = $dataNonAssoc( $data, false );
        $columns = implode( ',', array_keys( $data ) );
        $values = implode( ',', array_values( $data ) );

        if ( ! empty( $this->limit ) )
        {
            $limitSql = implode( $this->limit );
            $sql = sprintf( '(%s) VALUES (%s) LIMIT %s', $columns, $values, $limitSql );
        }
        else
        {
            $sql = sprintf( '(%s) VALUES (%s)', $columns, $values ); 
        }
        
        $this->insert[] = $sql;
        $this->lastQueryString[] = $sql;

        return $this;
    }

    public function from ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->_beforeCall( $data, $len );
        if ( getComponents( 'common' )->isAssocArray( $data ) )
        {
            $dataAssoc = dataKeyAssocInstance();
            $dataAssoc->valueDelimiter = '`';
            $dataAssoc->operator = ' ';
            $this->_afterCall( $callable, $dataAssoc );

            $data = $dataAssoc( $data );
        }
        else
        {
            $dataNonAssoc = dataKeyInstance();
            $this->_afterCall( $callable, $dataNonAssoc );
            $data = $dataNonAssoc->keyMap( $data );
        }

        $this->from[] = $data;
        return $this;
    }

    /** @return array|string */
    private function _where ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null )
    {
        $this->_beforeCall( $data, $len );
        $dataAssoc = dataKeyAssocInstance();

        if ( null !== $type )
        {
            $dataAssoc->toStringSepChar = " {$type} ";
        }

        $this->_afterCall( $callable, $dataAssoc );

        return $dataAssoc( $data );
    }

    public function andWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->andWhere[] = $this->_where( $data, $callable, $len, 'AND' );
        return $this;
    }

    public function orWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->orWhere[] = $this->_where( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function where ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->where[] = $this->_where( $data, $callable, $len );
        return $this;
    }

    public function join ( array $data, ?array $on = null, ?string $onType = null, ?Closure $callable = null, int $len = 1 ) : self
    {
        $this->_beforeCall( $data, $len );

        if ( getComponents( 'common' )->isAssocArray( $data ) )
        {
            $dataAssoc = dataKeyAssocInstance();
            $dataAssoc->valueDelimiter = '`';
            $dataAssoc->operator = ' ';
            $this->_afterCall( $callable, $dataAssoc );

            $data = $dataAssoc( $data );
        }
        else
        {
            $dataNonAssoc = dataKeyInstance();
            $this->_afterCall( $callable, $dataNonAssoc );

            $data = $dataNonAssoc->keyMap( $data );
        }

        $this->join[] = $data;

        if ( null !== $on )
        {
            $onType = null !== $onType ? strtolower( $onType ) : '';

            if ( $onType == 'or' )
                $this->orOn( $on );
            else if ( $onType == 'and' )
                $this->andOn( $on );
            else
                $this->on( $on );
        }

        return $this;
    }

    public function set ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->_beforeCall( $data, $len );
        $dataAssoc = dataKeyAssocInstance();
        $this->_afterCall( $callable, $dataAssoc );

        $this->set[] = $dataAssoc( $data );
        return $this;
    }

    public function limit ( int $before = 0, int $after = 0, ?Closure $callable = null ) : self
    {
        $before = $before > 0 ? $before : '';
        $after  = $after  > 0 ? ", $after" : '';
        $str = sprintf( '%s%s', $before, $after );

        if ( '' !== $str )
        { // $str = $this->_afterCall( $str, $callable );
            $this->limit[] = $str;
        }

        return $this;
    }

    public function is ()
    {
        $char = 'IS';
    }
    public function not ()
    {
        $char = 'NOT';
    }

    private function _on ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null ) : string
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataAssoc = dataKeyAssocInstance();
        $dataAssoc->valueDelimiter = '`';

        if ( null !== $type )
        {
            $type = trim( $type );
            $dataAssoc->toStringSepChar = " {$type} ";
        }

        $this->_afterCall( $callable, $dataAssoc );

        return $dataAssoc( $data );
    }

    public function andOn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->andOn[] = $this->_on( $data, $callable, $len, 'AND' );
        return $this;
    }

    public function orOn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->orOn[] = $this->_on( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function on ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->on[] = $this->_on( $data, $callable, $len );
        return $this;
    }

    public function in ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $dataNonAssoc->keyDelimiter = '\'';
        $this->_afterCall( $callable, $dataNonAssoc );

        $this->in[] = $dataNonAssoc->keyMap( $data );
        return $this;
    }

    public function orderBy ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );

        /** Validate Data */
        $mapFn = function ( string $str ) : bool {
            return ! in_array( strtoupper( $str ), [ 'ASC', 'DESC' ] );
        };
        if ( [] !== array_filter( $data, $mapFn ) )
        {
            $error = 'Values of $data variable must be in: [ DESC, ASC ].';
            throw new ErrorArrayException( $error );
        }
        /** End validate Data */

        $dataAssoc = dataKeyAssocInstance();
        $dataAssoc->valueDelimiter = '';
        $dataAssoc->operator = ' ';
        $this->_afterCall( $callable, $dataAssoc );

        $this->orderBy[] = $dataAssoc( $data );
        return $this;
    }

    public function null ()
    {
        $char = 'NULL';
    }

    /** @throws ErrorArrayException */
    private function _beforeCall ( array $data, int $len ) : void
    {
        if ( count( $data ) > $len )
        {
            throw new ErrorArrayException;
        }
    }

    private function _afterCall ( ?Closure $callable = null, ?object &$dataFilter = null ) :void
    {
        if ( is_callable( $callable ) )
        {
            call_user_func( $callable, $dataFilter );
        }
    }

    public function __toString()
    {
        return var_export( get_object_vars( $this ), true );
    }

    public function __toArray()
    {
        return get_object_vars( $this );
    }
}