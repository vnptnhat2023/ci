<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Data;

use Red2Horse\Exception\ErrorArrayException;
use Red2Horse\Exception\ErrorParameterException;
use Red2Horse\Exception\ErrorPropertyException;

use function Red2Horse\Mixins\Functions\Instance\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/**
 * __toString, __invoke
 * @param ?\Closure $callable Escape needle
 * @return array|string $a = `a`(.`a`)* = 'a'(.'a')* | [$a] = [$a]
 */
class DataAssocKeyMap
{
    public      string       $keyDelimiter            = '`';
    public      string       $valueDelimiter          = '\'';
    public      string       $operator                = '=';
    private     string       $combineChar             = '.';
    public      int          $combineLimitChar        = 10;
    public      string       $escapeChar              = '\'';
    public      bool         $escapeKeys              = true;
    public      bool         $escapeValues            = true;
    private     array        $specialChars            = [
        '~','`','!','@','#','$','%','^','&','*','(',')',
        '-','_','+','=','[','{',']','}','\\','|',
        ';',':','"','\'',',','<','>','.','/','?','/',
    ];
    public      string       $toStringSepChar         = ', ';
    public      string       $toStringSepEndChar      = '';
    // public int $limit = 10;
    public      array        $data;
    public      bool         $useExplodeCombine       = true;
    public      bool         $useTranslate            = true;
    // Before_exploded
    protected   array        $methods                 = [
        'before_string_combine_exploded'        => \Red2Horse\Mixins\Classes\Data\DataArrayEventsClass::class,
        'before_set_no_explode'                 => \Red2Horse\Mixins\Classes\Data\DataArrayEventsClass::class
    ];
    /** @var    string[]     $dataNoExplode */
    private     array        $dataNoExplode           = [];
    /** @var    string[]     $keyValueNoExplode */
    protected   array        $keyValueNoExplode       = [];
    /** @var    string[]     $keyNoExplode */
    protected   array        $keyNoExplode            = [];
    /** @var    string[]     $valueNoExplode */
    protected   array        $valueNoExplode          = [];
    private     string       $toStr                   = '';
    private     bool         $mapped                  = false;
    private     int          $dataLen;
    private     string       $keyPattern              = '/`?[\w_]+`?|\s{1,3}+AS{1,3}\s+|`?\*{1}`?|\*{1}/';
    private     string       $valuePattern            = '/(\'|\")?[\w_]+(\'|\")?|\s{1,3}+AS{1,3}\s+/';

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

    /** Set trigger property */
    public function setMethods ( array $data ) : void
    {
        $this->methods = array_merge( $this->methods, $data );
    }

    /** Get trigger property */
    public function getMethods () : array
    {
        return $this->methods;
    }
    
    /** $before $str $after */
    public function stringCombine (
        string $before,
        string $str,
        string $after,
        bool $esc = true,
        string $format = '%1$s%2$s%3$s' ): string
    {
        $str                = trim( $str, ' ' );
        $common             = getComponents( 'common' );

        $combineCondition   = $this->useExplodeCombine 
            && preg_match( '/'. $this->getCombineChar() . '/', $str ) 
            && ! in_array( $str, $this->dataNoExplode, true );

        if ( $combineCondition )
        {
            $str        = $this->_trigger( 'before_string_combine_exploded', $str, $this->getCombineChar() );

            $exploded   = explode( $this->combineChar, $str, $this->combineLimitChar );

            $exploded   = $this->_trigger( 'after_string_combine_exploded', $exploded );

            $mapFn      = fn( $mapStr ) => $this->stringCombine( $before, $mapStr, $after, $esc, $format );
            
            /** @var array $map */
            $map = array_map( $mapFn, $exploded );

            /** @var string $str */
            return $str = implode( $this->combineChar, $map );
        }

        if ( $this->escapeChar === '\'' )
        {
            $isEsc = $esc ? $common->esc( $str ) : $str;
        }
        else if ( $this->escapeChar === '\%' )
        {
            $isEsc = $esc ? $common->escLike( $str ) : $str;
        }

        $return = ( '*' === $str ) 
            ? sprintf( $format, '', $isEsc, '' ) 
            : sprintf( $format, $before, $isEsc, $after );

        return $return;
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
     * @throws ErrorPropertyException
     * @param array $data Associative-only
     * @param ?callable $callable Escape needle
     * @return array|string $a = `a`(.`a`)* = 'a'(.'a')* | [$a] = [$a]
     */
    public function __invoke( array $data, bool $toString = true, ?callable $callable = null )
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorPropertyException( 'Property: $data cannot empty.', 406 );
        }

        $this->data                 = $data;
        $this->dataLen              = count( $data ) - 1;
        $data                       = [];
        $i                          = 0;

        $dataNonAssoc               = new DataKeyMap;
        $dataNonAssoc->keyDelimiter = '\'';
        $dataNonAssoc->initDataAssocKeyMap( clone $this );

        foreach ( $this->data as $key => $value )
        {
            if ( is_array( $value ) )
            {
                $value  = $dataNonAssoc->keyMap( $value );
                $value  = sprintf( '%s%s%s', '(' , $value, ')' );
            }

            $key        = ( string ) $key;
            $value      = ( string ) $value;

            $this->_filterNoExplode( $key, $value );

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
     * @throws ErrorParameterException
     */
    public function matchKey ( string $str ) : string
    {
        if ( ! preg_match( $this->keyPattern, $str ) )
        {
            $errorParameter = sprintf( 'Invalid format for $key: %s', $str );
            throw new ErrorParameterException( $errorParameter );
        }

        return $str;
    }

    /**
     * @throws ErrorParameterException
     */
    public function matchValue ( string $str ): string
    {
        if ( ! preg_match( $this->valuePattern, $str ) )
        {
            $errorParameter = sprintf( 'Invalid format for $value: %s', $str );
            throw new ErrorParameterException( $errorParameter );
        }

        return $str;
    }

    /**
     * @param string $key kv: KeyValueNoExplode, k: KeyNoExplode, v: ValueNoExplode
     * @param array|\stdClass|string $value
     */
    public function setNoExplode ( string $key = 'kv', $value ) : void
    {
        if ( ! is_string( $value ) && ! getComponents( 'common' )->nonAssocArray( $value ) )
        {
            $errorParameter = 'Parameter 2: "value" must be a non-associative array';
            throw new ErrorParameterException( $errorParameter );
        }

        $array = [
            'kv'    => 'keyValueNoExplode',
            'k'     => 'keyNoExplode',
            'v'     => 'valueNoExplode'
        ];

        $key = $array[ $key ];
        $this->_trigger( 'before_set_no_explode', $key, $this->getCombineChar() );
        
        if ( ! is_string( $value ) )
        {
            $value = ( array ) $value;
            $this->$key = array_merge( $this->$key, $value );
        }
        else
        {
            $this->$key[] = $value;
        }

        $this->_trigger( 'after_set_no_explode', $key, $this->getCombineChar() );
    }

    public function getNoExplode () : array
    {
        return $this->dataNoExplode;
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

        $isAssocArray = getComponents( 'common' )->isAssocArray( $data );

        if ( $getAssoc && is_array( $data ) && $isAssocArray )
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
                'array'     =>  ( [] !== $data )    ? $data             : $default,
                'string'    =>  is_string( $data )  ? ( array ) $data   : $default,

                'object'    =>  ( function() use ( $data, $default )
                {
                    if ( $data instanceof \stdClass ) 
                    {
                        return ( array ) $data;
                    }
                    else
                    {
                        return method_exists( $data, 'toArray' ) 
                            ? $data->toArray() 
                            : $default;
                    }
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
    private function _filterNoExplode ( string $key, string $value ) : void
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
        $value = $this->stringCombine(
            $this->valueDelimiter,
            ( string ) $value,
            $this->valueDelimiter,
            $this->escapeValues
        );

        $key = $this->stringCombine(
            $this->keyDelimiter,
            ( string ) $key,
            $this->keyDelimiter,
            $this->escapeKeys
        );

        if ( $toString )
        {
            $sep = ( $i < $this->dataLen ) 
                ? $this->toStringSepChar 
                : $this->toStringSepEndChar;

            $this->toStr .= $this->publicToString( 
                $key, ( string ) $value, $sep 
            );
        }
        else
        {
            $data[ $key ] = $value;
        }
    }

    private function _trigger ( string $name, ...$args )
    {
        if ( ! $this->useTranslate )
        {
            return reset( $args );
        }

        if ( in_array( $name, $this->methods ) && method_exists( $this, $name ) )
        {
            return $this->$name( ...$args );
        }
        else if ( array_key_exists( $name, $this->methods ) )
        {
            $class = $this->methods[ $name ];
            $instance = new $class;

            if ( method_exists( $instance, $name ) )
            {
                return $instance->$name( ...$args );
            }
        }

        return reset( $args );
    }
}