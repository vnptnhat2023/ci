<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits\Object;

use function Red2Horse\Mixins\Functions\Instance\getInstance;

trait TraitInstanceTrigger
{
    /**
     * @param null|object|string
     * @param string[] $_triggerNames
     */
    private function _trigger ( $instance = null, array $_triggerNames, ...$args ) : array
    {
        if ( [] === $_triggerNames )
        {
            return reset( $args );
        }

        $data = [];
        if ( null === $instance )
        {
            $instance = $this;
        }
        else if ( is_string( $instance ) )
        {
            $instance = getInstance( $instance );
        }

        foreach ( $_triggerNames as $name )
        {
            if ( method_exists( $instance, $name ) )
            {
                $data[ $name ] = $instance->$name( ...$args );
            }
        }

        return $data;
    }
}