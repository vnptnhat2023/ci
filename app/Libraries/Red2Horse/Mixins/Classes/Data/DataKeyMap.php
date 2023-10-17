<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Data;

use Red2Horse\Exception\ErrorPropertyException;
use Red2Horse\Exception\ErrorParameterException;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/**
 * Assign string before elements array.
 */
class DataKeyMap
{
    public string $tbl;
    // public int $limit = 10;
    public array $map;

    private bool $mapped = false;

    public string $operator = '.';
    public string $keyDelimiter = '`';
    public string $valueDelimiter = '\'';

    public bool $isSelect = true;

    /**
     * @throws ErrorPropertyException
     * @param array $map Non-associative
     * @param ?callable $callable Escape needle
     * @return array|string
     */
    public function keyMap( array $map, bool $toString = true, ?callable $callable = null )
    {
        if ( empty( $map ) )
        {
            throw new ErrorPropertyException( 'Property: "$map" cannot empty' );
        }

        $this->map = $map;

        $i = 1;

        $mapFunc = function ( &$value ) use ( $callable, &$i )
        {
            if ( is_array( $value ) && ( $count = count( $value ) ) > 0 )
            {
                $res = $this->_keyMapIsArray( $value, $count );
            }
            else if ( is_callable( $callable ) )
            {
                $res = $callable( $value );
            }
            else
            {
                $res = sprintf( '%s', $this->matchString( ( string ) $value ) );
            }

            $i++;

            return $res;
        };

        $this->map = array_map( $mapFunc, $this->map );
        
        $this->mapped = true;
        $return = $toString ? $this->__toString() : $this->map;
        $this->mapped = false;

        return $return;
    }

    /**
     * @throws ErrorParameterException
     * @param array $map Non-associative
     * @param ?callable $callable Escape needle
     * @return array|string
     */
    public function __invoke( string $tbl, array $map, bool $toString = true, ?callable $callable = null )
    {
        if ( empty( $map ) || '' === $tbl )
        {
            throw new ErrorPropertyException( 'Property $map cannot empty' );
        }

        $this->tbl = $tbl;
        $this->map = $map;

        $i = 1;

        $mapFunc = function ( $value ) use ( $callable, &$i )
        {
            if ( is_array( $value ) )
            {
                if ( 'AS' === $value[ 1 ] ) { throw new ErrorParameterException; }
                $res = sprintf( '%s AS %s', $this->matchString( $value[ 0 ] ), $this->matchString( $value[ 2 ] ) );
            }
            else if ( is_callable( $callable ) )
            {
                $res = $callable( $this->tbl, $value );
            }
            else
            {
                $res = sprintf( '%s.%s', $this->matchString( $this->tbl ), $this->matchString( $value ) );
            }
            $i++;

            return $res;
        };

        $this->map = array_map( $mapFunc, $this->map );
        
        $this->mapped = true;
        $return = $toString ? $this->__toString() : $this->map;
        $this->mapped = false;

        return $return;
    }

    private DataAssocKeyMap $dataAssocKeyMap;

    private function matchString ( string $str ) : string
    {
        $arrayAssocKeyMap = $this->dataAssocKeyMap = new DataAssocKeyMap;
        $arrayAssocKeyMap->operator = $this->operator;

        $str = $arrayAssocKeyMap->stringCombine(
            $this->keyDelimiter,
            $arrayAssocKeyMap->matchKey( $str ),
            $this->keyDelimiter
        );

        return $str;
    }

    public function __toString () : string
    { 
        $this->mapped || $this->__invoke( $this->tbl, $this->map, false );
        return implode( ',', $this->map );
    }

    /** @param mixed $value */
    public function __set ( string $key, $value ) : void
    {
        if ( property_exists( $this, $key ) )
        {
            $this->dataAssocKeyMap->$key = $value;
        }
    }
    
    private function _keyMapIsArray ( array $value, int $count ) : string
    {
        if ( $count == 1 )
        {
            $res = sprintf( '%s', $this->matchString( ( string ) $value[ 1 ] ) );
        }
        else if ( $count == 2 )
        {
            $res = sprintf(
                '%s %s',
                $this->matchString( ( string ) $value[ 0 ] ),
                $this->matchString( ( string ) $value[ 1 ] )
            );
        }
        else
        {
            $key = $this->matchString( ( string ) $value[ 0 ] );

            if ( 'as' !== strtolower( $value[ 1 ] ) && 'distinct' !== strtolower( $value[ 1 ] ) )
            {
                $value = $this->matchString( ( string )  $value[ 1 ] );
            }
            else if ( 'as' === strtolower( $value[ 1 ] ) )
            {
                $value = sprintf( 'AS %s',  $this->matchString( ( string ) $value[ 2 ] ) );
            }
            else if ( 'distinct' === strtolower( $value[ 1 ] ) )
            {
                $value = sprintf( 'DISTINCT %s',  $this->matchString( ( string ) $value[ 2 ] ) );
            }

            $res = sprintf( '%s %s', $key, $value );
        }

        return $res;
    }
}