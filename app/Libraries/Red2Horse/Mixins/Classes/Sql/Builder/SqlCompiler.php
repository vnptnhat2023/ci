<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql\Builder;

use Red2Horse\Exception\ErrorArrayException;
use Red2Horse\Exception\ErrorParameterException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Data\dataKey;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlCompiler
{
    use TraitSingleton;

    private ?\stdClass $baseBuilder;

    public function __construct () { }

    public function init ( ?\stdClass $baseBuilder ) : self
    {
        $this->baseBuilder = $baseBuilder;
        return $this;
    }

    public function update ( int $len = 1/*, ?Closure $callable = null*/ ) : string
    {
        if ( empty( $setData = $this->baseBuilder->set ) )
        {
            throw new ErrorArrayException;
        }

        if ( $len > $this->baseBuilder->updateLimitRows )
        {
            throw new ErrorParameterException( sprintf( 'Argument "$len" %s > %s', $len, $this->baseBuilder->updateLimitRows ) );
        }

        $whereSql = $this->_compileWhere();

        /** SET sql string */
        $setSql    = implode( ', ', $setData );

        $isIn       = ! empty( $this->baseBuilder->in );
        $isJoin     = ! empty( $this->baseBuilder->join );
        $isJoinIn   = $isIn && $isJoin;

        /** Template config */
        $sqlConfig  = getConfig( 'sql' );

        if ( $isJoinIn )
        {
            $joinInTemplate = $sqlConfig->updateJoinInTemplate;
            $joinSql = implode( ', ', $this->baseBuilder->join );
            // add more ON
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', trim( $onSql ) );
            }
            $inSql = implode( ', ', $this->baseBuilder->in );

            $sql = sprintf( $joinInTemplate, $this->baseBuilder->table, $joinSql, $setSql, $whereSql, $inSql );
        }
        else if ( $isJoin )
        {
            $joinTemplate = $sqlConfig->updateJoinTemplate;
            $joinSql = implode( ', ', $this->baseBuilder->join );
            // add more ON
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', trim( $onSql ) );
            }

            $sql = sprintf( $joinTemplate, $this->baseBuilder->table, $joinSql, $setSql, $whereSql );
        }
        else if ( $isIn )
        {
            $inTemplate = $sqlConfig->updateInTemplate;
            $inSql = implode( ', ', $this->baseBuilder->in );

            $sql = sprintf( $inTemplate, $this->baseBuilder->table, $setSql, $whereSql, $inSql );
        }
        else
        {
            $updateTemplate = $sqlConfig->updateTemplate;

            $sql = sprintf( $updateTemplate, $this->baseBuilder->table, $setSql, $whereSql );
        }

        $sql .= " LIMIT $len";

        return $sql;
    }
    
    public function delete ( $len = 1/*, ?Closure $callable = null*/ ) : string
    {
        if ( $len > $this->baseBuilder->deleteLimitRows )
        {
            $error = sprintf( 'Argument "$len" %s > %s', $len, $this->baseBuilder->deleteLimitRows );
            throw new ErrorParameterException( $error );
        }

        $whereSql = $this->_compileWhere();

        $isIn       = ! empty( $this->baseBuilder->in );
        $isJoin     = ! empty( $this->baseBuilder->join );
        $isJoinIn   = $isIn && $isJoin;

        /** Template config */
        $sqlConfig  = getConfig( 'sql' );

        if ( $isJoinIn )
        {
            $joinInTemplate = $sqlConfig->deleteJoinInTemplate;
            $joinSql = implode( ', ', $this->baseBuilder->join );
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', $onSql );
            }

            $inSql = implode( ', ', $this->baseBuilder->in );

            $sql = sprintf( $joinInTemplate, $joinSql, $this->baseBuilder->table, $whereSql, $inSql );
        }
        else if ( $isJoin )
        {
            $joinTemplate = $sqlConfig->deleteJoinTemplate;
            $joinSql = implode( ', ', $this->baseBuilder->join );
            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', $onSql );
            }

            $sql = sprintf( $joinTemplate, $joinSql, $this->baseBuilder->table, $whereSql );
        }
        else if ( $isIn )
        {
            $inTemplate = $sqlConfig->deleteInTemplate;
            $inSql = implode( ', ', $this->baseBuilder->in );

            $sql = sprintf( $inTemplate, $this->baseBuilder->table, $whereSql, $inSql );
        }
        else
        {
            $deleteTemplate = $sqlConfig->deleteTemplate;

            $sql = sprintf( $deleteTemplate, $this->baseBuilder->table, $whereSql );
        }

        $sql .= " LIMIT $len";

        return $sql;
    }
    
    public function get () : string
    {
        $select = $this->_compileSelect();
        $from   = $this->_compileFrom();
        $join   = $this->_compileJoin();
        $on     = $this->_compileOn( false );
        $where  = $this->_compileWhere( false );
        $in     = $this->_compileIn();
        $order  = $this->_compileOrderBy();
        $limit  = $this->_compileLimit();

        $sql = sprintf(
            'SELECT %s FROM %s',
            '' === $select ? '*' : $select,
            '' === $from ? dataKey( [ $this->baseBuilder->table ] ) : $from
        );

        '' === $join    || $sql .= sprintf( ' JOIN %s', $join );
        '' === $on      || $sql .= sprintf( ' ON %s', $on );
        '' === $where   || $sql .= sprintf( ' WHERE %s', $where );
        '' === $in      || $sql .= sprintf( ' IN (%s)', $in );
        '' === $order   || $sql .= sprintf( ' ORDER BY %s', $order );
        '' === $limit   || $sql .= sprintf( ' LIMIT %s', $limit );

        return $sql;
    }

    public function insert ( array $data ) : string
    {
        $columns = implode( ',', array_keys( $data ) );
        $values = implode( ',', array_values( $data ) );
        $insertTemplate = getConfig( 'sql' )->insertTemplate;

        if ( ! empty( $this->baseBuilder->limit ) )
        {
            $limitSql = implode( $this->baseBuilder->limit );

            $sql = sprintf(
                $insertTemplate . ' LIMIT %s', 
                $this->baseBuilder->table, 
                $columns, 
                $values, 
                $limitSql
            );
        }
        else
        {
            $sql = sprintf( $insertTemplate, $this->baseBuilder->table, $columns, $values ); 
        }

        return $sql;
    }
    

    public function _compile ( string $propName, $throw = true ) : string
    {
        $orProp = 'or' . ucfirst( $propName );
        $andProp = 'and' . ucfirst( $propName );
        $data = $this->baseBuilder->{ $propName };
        $orData = $this->baseBuilder->{ $orProp };
        $andData = $this->baseBuilder->{ $andProp };

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
            $prefix = $emptyData && $emptyAndData ? '' : ' OR';
            $sql .= sprintf( '%s %s', $prefix, implode( $orData ) );
        }

        if ( ! $emptyAndData )
        {
            $prefix = $emptyData && $emptyOrData ? '' : ' AND';
            $sql .= sprintf( '%s %s', $prefix, implode( $andData ) );
        }

        return $sql;
    }
    
    public function _compileWhere ( $throw = true ) : string
    {
        return $this->_compile( 'where', $throw );
    }

    public function _compileOn ( $throw = true ) : string
    {
        return $this->_compile( 'on', $throw );
    }

    public function _compileOtherProps ( string $propName, string $implodeChar = ', ' ) : string
    {
        $str = '';
        if ( ! empty( $this->baseBuilder->$propName ) )
        {
            $str = implode( "{$implodeChar} ", $this->baseBuilder->$propName );
        }
        return $str;
    }

    public function _compileFrom ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'from', $implodeChar );
    }

    public function _compileJoin ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'join', $implodeChar );
    }

    public function _compileLimit ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'limit', $implodeChar );
    }

    public function _compileIn ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'in', $implodeChar );
    }

    public function _compileSelect ( string $implodeChar = ', ') : string
    {
        // $select = $this->_compileOtherProps( 'select', $implodeChar );

        // if ( '' !== $distinct = $this->_compileDistinct() )
        // {

        // } 
        return $this->_compileOtherProps( 'select', $implodeChar );
    }

    public function _compileDistinct ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'distinct', $implodeChar );
    }

    public function _compileOrderBy ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'orderBy', $implodeChar );
    }
}