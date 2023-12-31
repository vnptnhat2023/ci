<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits\Registry;

use Red2Horse\Exception\ErrorClassException;
use Red2Horse\Mixins\Classes\Registry\
{
    RegistryClass___,
    RegistryClass
};

/** State switch */
trait TraitRegistryClassMethod
{
    private string $keyName;

    /** Set switch state */
    private function configRegistryClassMethod___( string $keyName = RegistryClass___::class )
    {
        $this->keyName = $keyName;
    }

    public function delClass ( string $className ) : bool
    {
        return $this->keyName::delete( $className );
    }

    public function hasClass ( string $className ) : bool
    {
        return $this->keyName::has( $className );
    }

    public function getClass ( string $className, string $classKey = '' )
    {
        $classData = $this->instanceData( $className );

        if ( ! is_array( $classData ) )
        {
            return $classData;
        }

        return $classData[ $classKey ] ?? $classData;
    }

    /**
     * @throws ErrorClassException
     * @return mixed array|null : registryClass only
     */
    public function instanceData ( string $className, $params = null )
    {
        if ( null !== $classData = $this->keyName::get( $className ) )
        {
            return $classData;
        }

        if ( static::class != RegistryClass::class )
        {
            return null;
        }

        try
        {
            $className::getInstance( $params );
            return $this->keyName::get( $className );
        }
        catch ( \Exception $e )
        {
            throw new ErrorClassException( $e->getMessage(), 406 );
        }
    }

    /** @param mixed $value */
    public function setClass ( string $className, $value, bool $override = false ) : bool
    {
        $isset = $this->keyName::set( $className, $value, $override );
        return $isset;
    }
}