<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits\Object;

/**
 * @source https://www.php.net/manual/en/closure.bindto.php
 */
trait TraitBindTo
{
    public function __call ( $name, $args )
    {
        if ( is_callable( $this->$name ) )
        {
            return call_user_func( $this->$name, $args );
        }

        throw new \RuntimeException( "Method { $name } does not exist." );
    }
    
    public function __set ( $name, $value )
    {
        if ( is_callable( $value ) )
        {
            /** @var \Closure $value */
            $this->$name =  $value->bindTo( $this, $this );
        }

        $this->$name =  $value;
    }
}