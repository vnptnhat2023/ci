<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Interfaces\Object;

interface ReflectClass___Interface
{
    /** @return mixed the method result. */
    public function getMethod ( string $method, array $args = [] );

    /** @return array <string, result>[] key: name, value: result */
    public function getMethods () : array;
}