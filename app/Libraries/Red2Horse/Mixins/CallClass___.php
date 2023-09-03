<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins;

class CallClass___ {

    use TraitCall, TraitSingleton;

    public function __construct()
    {
        $this->traitCallback[ 'before' ] = true;
        $this->traitCallback[ 'after' ] = true;
        $this->traitCallback[ 'arguments' ] = [ 'argument 2e' ];

        $this->traitBeforePrefix = 'R2h_before_';
        $this->traitBeforePrefix = 'R2h_after_';
    }

}