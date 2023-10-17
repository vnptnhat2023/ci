<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Data;

use Red2Horse\Exception\ErrorArrayException;
use Red2Horse\Exception\ErrorParameterException;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/**
 * __toString, __invoke
 * @throws \Error
 * @param ?\Closure $callable Escape needle
 * @return array|string $a = `a`(.`a`)* = 'a'(.'a')* | [$a] = [$a]
 */
class DataAssocKeyMap
{
    public string $keyDelimiter = '`';
    public string $valueDelimiter = '\'';
    public string $operator = '=';

    private string $combineChar = '.';
    public int $combineLimitChar = 10;

    private array $specialChars = [
        '~','`','!','@','#','$','%','^','&','*','(',')',
        '-','_','+','=','[','{',']','}','\\','|',
        ';',':','"','\'',',','<','>','.','/','?','/',
    ];

    public string $toStringSepChar = ', ';
    public string $toStringSepEndChar = '';

    // public int $limit = 10;
    public array $data;

    /** @var string[] $dataNoExplode */
    public array $dataNoExplode = [];

    /** @var string[] $keyValueNoExplode */
    public array $keyValueNoExplode = [];

    /** @var string[] $keyNoExplode */
    public array $keyNoExplode = [];

    /** @var string[] $valueNoExplode */
    public array $valueNoExplode = [];

    private string $toStr = '';
    private bool $mapped = false;
    private int $dataLen;

    private $keyPattern = '/`?[\w_]+`?|\s{1,3}+AS{1,3}\s+|`?\*{1}`?|\*{1}/';
    private $valuePattern = '/(\'|\")?[\w_]+(\'|\")?|\s{1,3}+AS{1,3}\s+/';

    public function setCombineChar ( string $char = '.' ) : void
    {
        $this->combineChar = $char;
    }

    public function getCombineChar () : string
    {
        return in_array( $this->combineChar, $this->specialChars )
            ? sprintf( '\%s', $this->combineChar )
            : $this->combineChar;
    }
    
    /** $before $str $after */
    public function stringCombine (
        string $before,
        string $str,
        string $after,
        bool $esc = true,
        string $format = '%1$s%2$s%3$s' ): string
    {
        $str = trim( $str, ' ' );
        $common = getComponents( 'common' );

        $combineRegexChar = '/'. $this->getCombineChar() . '/';
        if ( preg_match( $combineRegexChar, $str ) && ! in_array( $str, $this->dataNoExplode, true ) )
        {
            $exploded = explode( $this->combineChar, $str, $this->combineLimitChar );
            $mapFn = fn( $mapStr ) 
                => $this->stringCombine( $before, $mapStr, $after, $esc, $format );
            
            /** @var array $map */
            $map = array_map( $mapFn, $exploded );

            /** @var string $str */
            return $str = implode( $this->combineChar, $map );
        }
        
        $isEsc = $esc ? $common->esc( $str ) : $str;
        return sprintf( $format, $before, $isEsc, $after );
    }

    /**
     * @param string $key "Key [ =, +, ...] $value $sep"
     */
    public function publicToString (
        string $key,
        string $value,
        string $sep = ', ',
        string $format = '%1$s%2$s%3$s%4$s' ) : string
    {
        return sprintf( $format, $key, $this->operator, $value, $sep );
    }

    public function __toString () : string
    { 
        $this->mapped || $this->__invoke( $this->data, false );
        return $this->toStr;
    }

    /**
     * @throws \Error
     * @param array $data Associative-only
     * @param ?callable $callable Escape needle
     * @return array|string $a = `a`(.`a`)* = 'a'(.'a')* | [$a] = [$a]
     */
    public function __invoke( array $data, bool $toString = true, ?callable $callable = null )
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new \Error( 'Property: $data cannot empty.', 406 );
        }

        $this->data = $data;
        $this->dataLen = count( $data ) - 1;
        $data = [];
        
        $i = 0;

        foreach ( $this->data as $key => $value )
        {
            $key = ( string ) $key;
            $value = ( string ) $value;

            $this->_beforeInvoke( $key, $value );

            if ( is_callable( $callable ) )
            {
                /** $this->data[] */
                $this->_callableInvoke( $key, $value, $toString, $callable );
            }
            else
            {
                /** $data */
                $this->_notCallableInvoke( $data, $key, $value, $toString, $i );
            }

            $i++;
        }

        $this->data = $data;

        $this->mapped = true;
        $return = $toString ? $this->toStr : $this->data;
        $this->mapped = false;

        return $return;
    }

    /**
     * @throws \Error
     */
    public function matchKey ( string $str ) : string
    {
        if ( ! preg_match( $this->keyPattern, $str ) )
        {
            throw new ErrorParameterException( sprintf( 'Invalid format for $key: %s', $str ) );
        }

        return $str;
    }

    /**
     * @throws \Error
     */
    public function matchValue ( string $str ): string
    {
        if ( ! preg_match( $this->valuePattern, $str ) )
        {
            throw new ErrorParameterException( sprintf( 'Invalid format for $value: %s', $str ) );
        }

        return $str;
    }

    /** CastToAssoc */
    private static array $castTypes = [
        'array',
        'string',
        'object'
    ];

    private static $castToAssocInit = false;
    /**
     * @param mixed $data
     */

    public static function castToAssoc ( $data, array $default = [], bool $getAssoc = true ) : array
    {
        if ( empty( $data ) || $data === $default )
        {
            return $default;
        }

        if ( $getAssoc && is_array( $data ) && getComponents( 'common' )->isAssocArray( $data ) )
        {
            return $data;
        }

        if ( self::$castToAssocInit )
        {
            self::$castToAssocInit = false;
            return $default;
        }

        if ( in_array( $getType = gettype( $data ), self::$castTypes ) )
        {
            $castTypes = [
                'array' => ( [] !== $data ) ? $data : $default,
                'string' => is_string( $data ) ? ( array ) $data : $default,
                'object' => ( function() use ( $data, $default ) {
                    if ( $data instanceof \stdClass ) return ( array ) $data;
                    else return method_exists( $data, 'toArray' ) ? $data->toArray() : $default;
                })()
            ];

            if ( ! $arrayData = $castTypes[ $getType ] )
            {
                return $arrayData;
            }
            else
            {
                self::$castToAssocInit = true;
                return self::castToAssoc( $arrayData, $default, $getAssoc );
            }
        }

        return $default;
    }

    /** Invoke methods private. */

    /**
     * No explode
     */
    private function _beforeInvoke ( string $key, string $value ) : void
    {
        $this->matchKey( $key );
        $this->matchKey( $value );

        if ( in_array( $key, $this->keyValueNoExplode, true ) )
        {
            $this->dataNoExplode[] = $value;
        }

        if ( [] !== $this->keyNoExplode || [] !== $this->valueNoExplode )
        {
            $this->dataNoExplode = array_merge(
                $this->dataNoExplode,
                $this->keyNoExplode,
                $this->valueNoExplode
            );
        }
    }

    private function _callableInvoke ( string $key, string $value, bool $toString, ?\Closure $callable ) : void
    {
        $common = getComponents( 'common' );
        /** Escape needle */
        $callableData = $callable( $key, $value );

        if ( $toString )
        {
            if ( ! is_string( $callableData ) )
            {
                throw new ErrorArrayException;
            }

            $this->toStr .= $callableData;
        }
        else
        {
            $key = ( string ) \array_key_first( $callableData );
            $value = ( string ) $callableData[ $key ];

            $this->data[ $common->esc( $key ) ] = $common->esc( $value );
        }
    }

    private function _notCallableInvoke ( array &$data, string $key, string $value, bool $toString, int $i ) : void
    {
        $esc = in_array( $key, getConfig( 'sql' )->excerptEsc, true );
        $value = $this->stringCombine(
            $this->valueDelimiter,
            ( string ) $value,
            $this->valueDelimiter,
            ! $esc
        );

        $key = $this->stringCombine(
            $this->keyDelimiter,
            ( string ) $key,
            $this->keyDelimiter
        );

        if ( $toString )
        {
            $sep = ( $i < $this->dataLen ) ? $this->toStringSepChar : $this->toStringSepEndChar;
            $this->toStr .= $this->publicToString( $key, ( string ) $value, $sep );
        }
        else
        {
            $data[ $key ] = $value;
        }
    }
}