<?php

declare ( strict_types = 1 );
namespace Red2Horse\Mixins;

trait TraitRegistry {

    /** @var <string, <string, string>[]>[] $traitRegistryData */
    public static array $traitRegistryData = [];

    final public static function set ( string $key, array $value, bool $override = false ) : bool
    {
        if ( $override || empty( self::$traitRegistryData[ $key ] ) )
        {
            self::$traitRegistryData[ $key ] = $value;
            return true;
        }

        return false;
    }

    /** @return null|array */
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
}