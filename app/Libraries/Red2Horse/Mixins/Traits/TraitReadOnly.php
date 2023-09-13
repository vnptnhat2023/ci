<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits;

trait TraitReadOnly
{
    public function __get( string $propName )
    {
        return $this->{$propName};
    }
}