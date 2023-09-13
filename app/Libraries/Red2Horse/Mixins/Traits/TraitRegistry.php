<?php

declare ( strict_types = 1 );
namespace Red2Horse\Mixins\Traits;

trait TraitRegistry
{
    private static array $traitRegistryData = [];

    /** @param mixed @value */
    final public static function set ( string $key, $value, bool $override = false ) : bool
    {
        if ( $override || empty( self::$traitRegistryData[ $key ] ) )
        {
            self::$traitRegistryData[ $key ] = $value;
            return true;
        }

        return false;
    }

    /** @return mixed */
    final public static function get ( string $key )
    {
        return self::$traitRegistryData[ $key ] ?? null;
    }

    final public static function delete ( string $key ) : bool
    {
        if ( array_key_exists( $key, self::$traitRegistryData ) )
        {
            unset( self::$traitRegistryData[ $key ] );
            return true;
        }

        return false;
    }

    public static function _debugInfo() : array
    {
        return self::$traitRegistryData;
    }
}