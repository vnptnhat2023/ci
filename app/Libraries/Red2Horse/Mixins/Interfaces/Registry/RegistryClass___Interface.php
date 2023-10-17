<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces\Registry;

interface RegistryClass___Interface
{
    public static function set ( string $key, array $value, bool $override = false ) : bool;

    /** @return null|array */
    public static function get ( string $key );

    public static function delete ( string $key ) : bool;
}