<?php
declare( strict_types = 1 );
namespace Red2Horse\Mixins;
/**
 * Reflections public method only
 */
class ReflectClass___ {

    protected object $ins;

    /** @param object|string $int */
    public function __construct ( $ins )
    {
        $this->ins = is_object( $ins ) ? $ins : new $ins;
    }

    /** @return mixed the method result. */
    function getMethod ( string $method, array $args = [] )
    {
        $reflection = new \ReflectionMethod( $this->ins, $method );
        $pass = [];

        foreach( $reflection->getParameters() as $param )
        { /* @var $param ReflectionParameter */
            $pass[] = $args[ $param->getName() ] ?? $param->getDefaultValue();
        }

        return $reflection->invokeArgs( $this->ins, $pass );
    }

    /**
     * @return array <string, result>[] key: name, value: result
     */
    function getMethods ()
    {
        $methods = ( new \ReflectionClass( $this->ins ) )->getMethods( \ReflectionMethod::IS_PUBLIC );
        $pass = [];

        foreach ( $methods as $method ) {
            $pass[ $method->name ] = $this->getMethod( $method->name );
        }

        unset( $this->ins );
        return $pass;
    }
}