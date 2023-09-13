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
        $this->traitUseBefore = $arguments[ 'traitCallback' ][ 'before' ] ?? false;
        $this->traitUseAfter = $arguments[ 'traitCallback' ][ 'after' ] ?? false;

        $this->run( $className );
    }
}