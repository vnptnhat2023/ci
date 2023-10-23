<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits\Object;

trait TraitInstanceTrigger
{
    /**
     * @param string[] $_triggerNames
     */
    private function _trigger ( ?object $instance = null, array $_triggerNames, ...$args ) : array
    {
        if ( [] === $_triggerNames )
        {
            return reset( $args );
        }

        $data = [];
        $instance = $instance ?: $this;

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