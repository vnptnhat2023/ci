<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits\Object;

trait TraitInstanceTrigger
{
    /**
     * @param null|object $instanced
     * @param string[] $calls
     * @return mixed
     */
    public function traitInstanceTrigger( ?object $instanced, array $calls, ...$args )
    {
        if ( null === $calls )
        {
            return $args;
        }

        $calls = ( array ) $calls;

        /** @var string $value */
        foreach ( $calls as $value )
        {
            if ( method_exists( $instanced, $value ) )
            {
                $data = $instanced->{ $value }( ...$args );
            }
            else if ( function_exists( $value ) )
            {
                $data = $value( ...$args );
            }
        }
    }
}