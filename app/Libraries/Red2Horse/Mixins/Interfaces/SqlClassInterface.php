<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces;

interface SqlClassInterface
{
    public function reInit () : void;

    /** @return mixed */
    public function getData ( ?string $key = null );

    /** @return mixed */
    public function getTable( string $key );

    public function setTable( string $table, string $name ) : bool;

    /** @return mixed */
    public function getColumn( string $key )/* : mixed*/;

    public function setColumn( string $key, string $value ) : bool;

    public function getFields( array $keys, string $table ) : array;
    /** @return mixed */
    public function getField( string $key, string $table )/* : mixed*/;

    public function setField( string $key, string $table, $value ) : bool;
}