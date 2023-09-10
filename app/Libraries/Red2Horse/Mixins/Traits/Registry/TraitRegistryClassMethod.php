<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Traits\Registry;

use Red2Horse\Mixins\Classes\Registry\RegistryClass___;

trait TraitRegistryClassMethod
{
    private string $keyName;

    private function configRegistryClassMethod___( string $keyName = RegistryClass___::class )
    {
        $this->keyName = $keyName;
    }

    public function delClass ( string $className ) : bool
    {
        return $this->keyName::delete( $className );
    }

    public function getClass ( string $className, string $classKey = '' )
    {
        $classData = $this->instanceData( $className );
        return $classData[ $classKey ] ?? $classData;
    }

    /**
     * @return array|null
     */
    public function instanceData ( string $className )
    {
        if ( null !== $classData = $this->keyName::get( $className ) )
        {
            return $classData;
        }
        
        try
        {
            $className::getInstance();
            $classData = $this->keyName::get( $className );
        }
        catch ( \Throwable $th )
        {
            throw $th;
        }

        return $classData;
    }

    /** @param mixed $value */
    public function setClass ( string $className, $value, bool $override = false ) : bool
    {
        return $this->keyName::set( $className, $value, $override );
    }
}