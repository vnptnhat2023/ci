<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes;

use Red2Horse\Mixins\Interfaces\CallClass___Interface;
use Red2Horse\Mixins\Traits\{
    TraitCall,
    TraitSingleton
};

class CallClass___ implements CallClass___Interface
{
    use TraitCall, TraitSingleton;

    public function __construct( $className = null, array $arguments = [] )
    {
        $this->traitCallback[ 'before' ] = $arguments[ 'traitCallback' ][ 'before' ] ?? false;
        $this->traitCallback[ 'after' ] = $arguments[ 'traitCallback' ][ 'after' ] ?? false;

        $this->run( $className );
    }
}