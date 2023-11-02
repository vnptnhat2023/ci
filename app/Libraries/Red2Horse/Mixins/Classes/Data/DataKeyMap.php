<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Data;

use Red2Horse\Exception\ErrorPropertyException;
use Red2Horse\Exception\ErrorParameterException;

use Red2Horse\Mixins\Classes\Data\DataAssocKeyMap;

use function Red2Horse\Mixins\Functions\Instance\getInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/**
 * Assign string before elements array.
 */
class DataKeyMap
{
    public      string       $format                    = '%1$s.%2$s';
    public      string       $tbl;
    // public   int          $limit                     = 10;
    public      array        $map;
    private     bool         $mapped                    = false;
    public      string       $operator                  = '.';
    public      string       $keyDelimiter              = '`';
    // public   string       $valueDelimiter            = '\'';
    public      string       $toStringSepChar           = ', ';
    public      string       $toStringSepEndChar        = '';
    public      bool         $useTranslate              = true;
    public      array        $methods                   = [
        'before_key_map_callback' => \Red2Horse\Mixins\Classes\Data\DataArrayEventsClass::class
    ];
    // public bool $isSelect = true;
    private                  DataAssocKeyMap            $dataAssocKeyMap;
    public                   bool                       $useExplodeCombine = true;

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
            throw new ErrorPropertyException( 'Property: "map" cannot be empty' );
        }

        $this->map = $map;
        $i = 1;

        $mapFunc = function ( &$value ) use ( $callable, &$i )
        {
            $value = $this->_trigger(
                'before_key_map_callback', 
                $value, 
                $this->initDataAssocKeyMap()->getCombineChar() 
            );

            if ( is_array( $value ) && ( $count = count( $value ) ) > 0 )
            {
                $res = $this->_keyMapIsArray( $value, $count );
            }
            else if ( is_callable( $callable ) )
            {
                $res = $callable( $this, $value );
            }
            else
            {
                $res = sprintf( '%s', $this->matchString( ( string ) $value ) );
            }

            $i++;

            $res = $this->_trigger(
                'after_key_map_callback', $res, $this->initDataAssocKeyMap()->getCombineChar()
            );
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
            $this->_trigger( 'before_key_map_invoke_callback', $value );

            if ( is_array( $value ) )
            {
                if ( 'AS' === $value[ 1 ] ) 
                { 
                    throw new ErrorParameterException; 
                }

                $res = sprintf(
                    '%s AS %s', 
                    $this->matchString( $value[ 0 ] ), 
                    $this->matchString( $value[ 2 ] ) 
                );
            }
            else if ( is_callable( $callable ) )
            { // need esc
                $res = $callable( $this, $this->tbl, $value );
            }
            else
            {
                if ( $value === '*' )
                {
                    $res = sprintf( 
                        $this->format,
                        $this->matchString( $this->tbl ), 
                        '*' 
                    );
                }
                else
                {
                    $value              = $this->matchString( $value ) ;

                    $oldKeyDelimiter    = $this->keyDelimiter;
                    $this->keyDelimiter = '';

                    $tblString          = $this->matchString( $this->tbl );

                    $this->keyDelimiter = $oldKeyDelimiter;
                    $res = sprintf( $this->format, $tblString, $value );
                }
            }
            $i++;

            $this->_trigger( 'after_key_map_invoke_callback', $value );
            return $res;
        };

        $this->map = array_map( $mapFunc, $this->map );
        
        $this->mapped = true;
        $return = $toString ? $this->__toString() : $this->map;
        $this->mapped = false;

        return $return;
    }

    public function __toString () : string
    { 
        $this->mapped || $this->__invoke( $this->tbl, $this->map, false );
        return implode( $this->toStringSepChar, $this->map );
    }

    private function matchString ( string $str, ?\Closure $callable = null ) : string
    {
        $arrayAssocKeyMap                       = $this->initDataAssocKeyMap();
        $arrayAssocKeyMap->operator             = $this->operator;
        $arrayAssocKeyMap->toStringSepChar      = $this->toStringSepChar;
        $arrayAssocKeyMap->toStringSepEndChar   = $this->toStringSepEndChar;
        $arrayAssocKeyMap->useExplodeCombine    = $this->useExplodeCombine;
        
        if ( null !== $callable )
        {
            $arrayAssocKeyMap = $callable( $arrayAssocKeyMap );
        }

        $str = $arrayAssocKeyMap->stringCombine(
            $this->keyDelimiter,
            $arrayAssocKeyMap->matchKey( $str ),
            $this->keyDelimiter
        );

        return $str;
    }

    public function initDataAssocKeyMap ( ?DataAssocKeyMap $assocKeyMap = null ) : DataAssocKeyMap
    {
        if (  null !== $assocKeyMap )
        {
            $assocKeyMap->useExplodeCombine     = false;
            $this->dataAssocKeyMap              = $assocKeyMap;

            return $assocKeyMap;
        }

        if ( ! isset( $this->dataAssocKeyMap))
        {
            $this->dataAssocKeyMap = new DataAssocKeyMap;
        }

        return $this->dataAssocKeyMap;
    }

    private function _keyMapIsArray ( array $value, int $count ) : string
    {
        if ( $count === 1 )
        {
            $key = $value[ 0 ];
            $res = sprintf( '%s', $this->matchString( ( string ) $key ) );
        }
        else if ( $count === 2 )
        {
            $key = $value[ 0 ];
            $res = sprintf(
                '%s %s',
                $this->matchString( ( string ) $key ),
                $this->matchString( ( string ) $value[ 1 ] )
            );
        }
        else
        {
            $key = $value[ 0 ];

            if ( 'as' !== strtolower( $value[ 1 ] ) && 'distinct' !== strtolower( $value[ 1 ] ) )
            {
                $value = $this->matchString( ( string ) $value[ 1 ] );
            }
            else if ( 'as' === strtolower( $value[ 1 ] ) )
            {
                $matchString = $this->matchString( ( string ) $value[ 2 ] );
                $value = sprintf( 'AS %s', $matchString );
            }
            else if ( 'distinct' === strtolower( $value[ 1 ] ) )
            {
                $matchString = $this->matchString( ( string ) $value[ 2 ] );
                $value = sprintf( 'DISTINCT %s', $matchString );
            }

            $res = sprintf( '%s %s', $this->matchString( ( string ) $key ), $value );
        }

        $this->_trigger( 'after_match_string', $res );
        return $res;
    }

     /** @return mixed */
    private function _trigger ( string $name, ...$args )
    {
        if ( ! $this->useTranslate || ! $this->initDataAssocKeyMap()->useTranslate )
        {
            return reset( $args );
        }

        if ( in_array( $name, $this->methods ) && method_exists( $this, $name ) )
        {
            return $this->$name( ...$args );
        }
        else if ( array_key_exists( $name, $this->methods ) )
        {
            $classNamespace = $this->methods[ $name ];
            $instance = getInstance( $classNamespace );

            if ( method_exists( $instance, $name ) )
            {
                return $instance->$name( ...$args );
            }
        }

        return reset( $args );
    }
}