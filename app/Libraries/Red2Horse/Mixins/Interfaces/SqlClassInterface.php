<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces;

interface SqlClassInterface
{
    /** @return mixed */
    public function getData ( ?string $key = null );

    /** @return mixed */
    public function getTable( string $key );

    public function setTable( string $table, string $name ) : bool;

    /** @return mixed */
    public function getColumn( string $key )/* : mixed*/;

    public function setColumn( string $key, string $value ) : bool;

    /** @return mixed */
    public function getField( string $key, string $table )/* : mixed*/;

    public function setField( string $key, string $table, $value ) : bool;
}