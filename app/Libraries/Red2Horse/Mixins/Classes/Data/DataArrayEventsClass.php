<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Data;

use Red2Horse\Mixins\Classes\Sql\SqlClass;
// use Red2Horse\Mixins\Traits\Object\TraitBindTo;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Sql\getTable;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class DataArrayEventsClass
{
    use TraitSingleton;
    // use TraitBindTo;

    public function __construct () { }

    private function _stringCharFilter ( string $combineString, string $combineChar ) : bool
    {
        $pattern = sprintf( '/%s/', $combineChar );
        return ( bool ) preg_match( $pattern, $combineString );
    }

    public function before_string_combine_exploded ( string $combineString, string $combineChar ) : string
    {
        if ( ! $this->_stringCharFilter( $combineString, $combineChar ) )
        {
            return $combineString;
        }

        $exploded = explode( '.', $combineString, 2 );
        $sqlClass = getInstance( SqlClass::class );

        // '*' === $combineString || 
        if ( ! $sqlClass->getTable( $exploded[ 0 ], false, false ) )
        {
            return $combineString;
        }

        if ( '*' === $exploded[ 1 ] )
        {
            return sprintf( '%s.%s', $exploded[ 0 ], $exploded[ 1 ] );
        }

        if ( $hasField = $sqlClass->getField( $exploded[ 1 ], $exploded[ 0 ], false, true, false ) )
        {
            return $hasField;
        }

        return $combineString;
    }

    /** BaseBuilder::[ From,Join ] */
    private ?string $for = null;
    private array $forArray = [ 'from', 'join' ];

    public function setFor ( ?string $value = null )
    {
        if ( in_array( $value, $this->forArray ) )
        {
            $this->for = $value;
        }
    }
    
    private function _fromAndJoinCallbackResponse ( $arrOrStr, string $combineChar )
    {
        if ( $this->_stringCharFilter( $arrOrStr, $combineChar ) )
        {
            return $this->before_string_combine_exploded( $arrOrStr, $combineChar );
        }
        else
        {
            return getTable( $arrOrStr, false, false, true );
        }
    }

    private function _fromAndJoinCallback (  $arrOrStr, string $combineChar )
    {
        if ( in_array( $this->for, [ 'from', 'join' ] ) )
        {
            $this->setFor( null );
            if ( is_string( $arrOrStr ) )
            {
                return $this->_fromAndJoinCallbackResponse( $arrOrStr, $combineChar );
            }

            if ( is_array( $arrOrStr ) && array_key_exists( 0, $arrOrStr ) )
            {
                $arrOrStr[ 0 ] = $this->_fromAndJoinCallbackResponse( $arrOrStr[ 0 ], $combineChar );
                return $arrOrStr;
            }
        }

        return $arrOrStr;
    }

    /**
     * @param array|string $arrOrStr
     * @return array|string
     */
    public function before_key_map_callback ( $arrOrStr, string $combineChar )
    {
        return $this->_fromAndJoinCallback( $arrOrStr, $combineChar );
    }
    /** End BaseBuilder::[ From,Join ] */
}