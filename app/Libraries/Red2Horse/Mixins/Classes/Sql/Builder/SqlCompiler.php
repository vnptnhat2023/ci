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
            $errorParaMeter = sprintf( 
                'Argument "$len" %s > %s',
                $len, 
                $this->baseBuilder->updateLimitRows 
            );
            throw new ErrorParameterException( $errorParaMeter );
        }

        $whereSql   = $this->_compileWhere();
        $setSql     = implode( ', ', $setData );
        $isJoin     = ! empty( $this->baseBuilder->join );

        /** Template config */
        $sqlConfig  = getConfig( 'sql' );

        if ( $isJoin )
        {
            $joinTemplate = $sqlConfig->updateJoinTemplate;
            $joinSql      = implode( ', ', $this->baseBuilder->join );

            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', $onSql );
            }

            $sql = sprintf(
                $joinTemplate,
                $this->baseBuilder->table,
                $joinSql,
                $setSql,
                $whereSql
            );
        }
        else
        {
            $updateTemplate = $sqlConfig->updateTemplate;
            $sql            = sprintf(
                $updateTemplate,
                $this->baseBuilder->table,
                $setSql,
                $whereSql
            );
        }

        if ( '' === $this->_compileLimit() )
        {
            $sql .= " LIMIT $len";
        }

        return $sql;
    }
    
    public function delete ( int $len = 1/*, ?Closure $callable = null*/ ) : string
    {
        if ( $len > $this->baseBuilder->deleteLimitRows )
        {
            $error = sprintf( 'Argument "$len" %s > %s', $len, $this->baseBuilder->deleteLimitRows );
            throw new ErrorParameterException( $error );
        }

        $whereSql = $this->_compileWhere();
        $isJoin     = ! empty( $this->baseBuilder->join );

        /** Template config */
        $sqlConfig  = getConfig( 'sql' );

        if ( $isJoin )
        {
            $deleteJoinTemplate = $sqlConfig->deleteJoinTemplate;
            $joinSql            = implode( ', ', $this->baseBuilder->join );

            if ( '' !== ( $onSql = $this->_compileOn( false ) ) )
            {
                $joinSql .= sprintf( ' ON %s', $onSql );
            }

            $sql = sprintf( $deleteJoinTemplate, $joinSql, $this->baseBuilder->table, $whereSql );
        }
        else
        {
            $deleteTemplate = $sqlConfig->deleteTemplate;
            $sql            = sprintf( $deleteTemplate, $this->baseBuilder->table, $whereSql );
        }

        if ( '' === $this->_compileLimit() )
        {
            $sql .= " LIMIT $len";
        }

        return $sql;
    }
    
    public function get () : string
    {
        $select         =       $this->_compileSelect();
        // $distinct       =       $this->_compileDistinct();
        $from           =       $this->_compileFrom();
        $join           =       $this->_compileJoin();
        $on             =       $this->_compileOn( false );
        $where          =       $this->_compileWhere();
        $orderBy        =       $this->_compileOrderBy();
        $limit          =       $this->_compileLimit();

        $sql = sprintf(
            'SELECT %s FROM %s',
            '' === $select ? '*' : $select,
            '' === $from ? dataKey( [ $this->baseBuilder->table ] ) : $from
        );

        // '' === $distinct    || $sql .= sprintf( ' SELECT distinct %s', $distinct );
        '' === $join        ||      $sql .= sprintf( ' JOIN %s', $join );
        '' === $on          ||      $sql .= sprintf( ' ON %s', $on );
        '' === $where       ||      $sql .= sprintf( ' WHERE %s', $where );
        '' === $orderBy     ||      $sql .= sprintf( ' ORDER BY %s', $orderBy );
        '' === $limit       ||      $sql .= sprintf( ' LIMIT %s', $limit );

        return $sql;
    }

    public function insert ( array $data ) : string
    {
        $columns        = implode( ',', array_keys( $data ) );
        $values         = implode( ',', array_values( $data ) );
        $insertTemplate = getConfig( 'sql' )->insertTemplate;
        $sql            = sprintf( $insertTemplate, $this->baseBuilder->table, $columns, $values );

        if ( ! empty( $this->baseBuilder->limit ) )
        {
            $sql = sprintf( '%s LIMIT %s', $sql, $this->_compileLimit() );
        }

        return $sql;
    }
    

    public function _compile ( string $propName, $throw = true ) : string
    {
        $orProp         = 'or' . ucfirst( $propName );
        $andProp        = 'and' . ucfirst( $propName );
        $data           = $this->baseBuilder->{ $propName };
        $orData         = $this->baseBuilder->{ $orProp };
        $andData        = $this->baseBuilder->{ $andProp };

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
            $prefix     = $emptyData && $emptyAndData ? '' : ' OR ';
            $sql        .= sprintf( '%s%s', $prefix, implode( $orData ) );
        }

        if ( ! $emptyAndData )
        {
            $prefix     = $emptyData && $emptyOrData ? '' : ' AND ';
            $sql        .= sprintf( '%s%s', $prefix, implode( $andData ) );
        }

        return $sql;
    }
    
    public function _compileWhere ( $throw = false ) : string
    {
        $text       = '';
        $where      = $this->_compile( 'where', $throw );
        $like       = $this->_compileLike();
        $null       = $this->_compileNullGeneral();
        $in         = $this->_compileInGeneral();

        '' === $where   || $text .= $where;
        '' === $like    || $text .= sprintf( '%s%s', '' !== $text ? ' OR '  : '', $like );
        '' === $null    || $text .= sprintf( '%s%s', '' !== $text ? ' OR '   : '', $null );
        '' === $in      || $text .= sprintf( '%s%s', '' !== $text ? ' OR '   : '', $in );

        return $text;
    }

    public function _compileOn ( $throw = true ) : string
    {
        $text = $this->_compile( 'on', $throw );
        return $text;
    }

    public function _compileLike ( $throw = false ) : string
    {
        return $this->_compile( 'like', $throw );
    }

    public function _compileInGeneral ( string $seperatorChar = ' OR') : string
    {
        $in             = $this->_compileIn();
        $notIn          = $this->_compileNotIn();

        $haveOr         = '' !== $in ? $seperatorChar : '';
        $seperator      = sprintf( '%s%s', $haveOr, $notIn );
        $haveNotIn      = '' !== $notIn ? $seperator : '';

        return sprintf( '%s%s', '' ?: $in, $haveNotIn );
    }

    public function _compileNullGeneral ( string $seperatorChar = ' OR') : string
    {
        $null           = $this->_compileNull();
        $notNull        = $this->_compileNotNull();

        $haveOr         = '' !== $null ? $seperatorChar : '';
        $seperator      = sprintf( '%s%s', $haveOr, $notNull );
        $haveNotNull    = '' !== $notNull ? $seperator : '';

        return sprintf( '%s%s', '' ?: $null, $haveNotNull );
    }
    

    public function _compileIn ( $throw = false ) : string
    {
        return $this->_compile( 'in', $throw );
    }

    public function _compileNotIn ( $throw = false ) : string
    {
        return $this->_compile( 'notIn', $throw );
    }

    public function _compileNull ( $throw = false ) : string
    {
        return $this->_compile( 'null', $throw );
    }

    public function _compileNotNull ( $throw = false ) : string
    {
        return $this->_compile( 'notNull', $throw );
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
        $text = $this->_compileOtherProps( 'join', $implodeChar );
        return $text;
    }

    public function _compileLimit ( string $implodeChar = ', ') : string
    {
        $text = $this->_compileOtherProps( 'limit', $implodeChar );
        return $text;
    }

    public function _compileSelect ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'select', $implodeChar );
    }

    public function _compileDistinct ( string $implodeChar = ', ') : string
    {
        return $this->_compileOtherProps( 'distinct', $implodeChar );
    }

    public function _compileOrderBy ( string $implodeChar = ', ') : string
    {
        $text = $this->_compileOtherProps( 'orderBy', $implodeChar );
        return $text;
    }
}