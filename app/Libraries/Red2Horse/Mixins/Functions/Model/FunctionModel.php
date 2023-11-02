<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Functions\Model;

use Red2Horse\Exception\ErrorClassException;
use Red2Horse\Facade\Query\QueryFacadeInterface;
use Red2Horse\Mixins\Classes\Registry\RegistryModelClass;
use Red2Horse\Mixins\Classes\Sql\Model;

use function Red2Horse\Mixins\Functions\NS\modelNamespace;
use function Red2Horse\Mixins\Functions\NS\registryNamespace;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function RegModelInstance () : RegistryModelClass
{
    return registryNamespace( 'RegistryModelClass' )::selfInstance();
}

/**
 * @return Model|\Red2Horse\Mixins\Traits\Object\TraitSingleton
 * @throws ErrorClassException
 */
function model ( 
    ?string $name = null,  
    ?string $table = null, 
    bool $getShared = false, 
    ?QueryFacadeInterface $connection = null ) : Model
{
    $namespace = str_replace( '/', '\\', modelNamespace( $name ) );

    if ( ! $getShared )
    {
        return new $namespace;
    }

    $instance = RegModelInstance();
    
    if ( $instance->hasClass( $namespace ) )
    {
        return $instance->instanceData( $namespace );
    }

    if ( ! $instance->setClass( $namespace, $namespace::getInstance() ) )
    {
        $errorClassException = sprintf( 'Cannot create instance from class: "%s"', $namespace );
        throw new ErrorClassException( $errorClassException );
    }

    $model = $instance->getClass( $namespace );
    // dd( $model, $instance );
    if ( null !== $table )
    {
        $model->init( $table, $connection );
    }

    return $model;
}

function BaseModel ( ?string $tableName = null, bool $getShared = false , ?QueryFacadeInterface $connection = null ) : Model
{
    $namespace = Model::class;

    if ( ! $getShared )
    {
        return new $namespace;
    }

    $instance = RegModelInstance();

    if ( $instance->hasClass( $namespace ) )
    {
        return $instance->instanceData( $namespace );
    }

    if ( ! $instance->setClass( $namespace, $namespace::getInstance() ) )
    {
        $errorClassException = sprintf( 'Cannot create instance from class: "%s"', $namespace );
        throw new ErrorClassException( $errorClassException );
    }

    $model = $instance->getClass( $namespace );

    if ( null !== $tableName )
    {
        $model->init( $tableName, $connection );
    }

    return $model;
}