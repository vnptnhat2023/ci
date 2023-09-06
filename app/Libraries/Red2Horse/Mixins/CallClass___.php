<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins;

class CallClass___ {

    use TraitCall, TraitSingleton;

    public function __construct( $className = null, array $arguments = [] )
    {
        $this->traitCallback[ 'before' ] = $arguments[ 'traitCallback' ][ 'before' ] ?? false;
        $this->traitCallback[ 'after' ] = $arguments[ 'traitCallback' ][ 'after' ] ?? false;

        $this->run( $className );
    }
}