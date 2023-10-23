<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql;

use Red2Horse\Exception\ErrorArrayException;
use Red2Horse\Facade\Query\QueryFacadeInterface;
use Red2Horse\Mixins\Interfaces\Sql\ModelInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Sql\getTable;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Model/* implements ModelInterface*/
{
    use TraitSingleton;

    protected BaseBuilder $builder;

    /** @var string[] */
    public array $allowedFields = [];

    /** @var <string, string> $createdAt [ 'create_at' => 'Y-m-d H:i:s' ] */
    public array $createdAt;
    public array $deletedAt;
    public array $updatedAt;
    public array $validTimeFormat = [ 'Y-m-d H:i:s', 'Y-m-d' ];

    public function __construct () {}

    public static function setTable ( string $table, ?QueryFacadeInterface $connection = null, \stdClass $childProperties = null ) : self
    {
        return getInstance( self::class )->table( $table, $connection, $childProperties );
    }

    public function table ( string $table, ?QueryFacadeInterface $connection = null, \stdClass $childProperties = null ) : self
    {
        $this->builder = getInstance( BaseBuilder::class, 'RegistryClass', false );
        $this->builder->table = getTable( $table, false, false, true );
        $this->builder->setConnection( $connection );
        $this->setModelProperty( $childProperties );

        return $this;
    } 

    public function setModelProperty ( \stdClass $childProperties = null )
    {
        $childProperties = $childProperties ?: $this->__toStdClass();
        if ( $childProperties->builder )
        {
            unset( $childProperties->builder );
        }

        $this->builder->setModelProperty( $childProperties );
    }

    public function __toString() : string
    {
        return var_export( get_object_vars( $this ), true );
    }

    public function __toArray() : array
    {
        return get_object_vars( $this );
    }

    public function __toStdClass () : \stdClass
    {
        return ( object ) $this->__toArray();
    }

    public function __call( string $method, array $args )
    {
        return $this->builder->$method( ...$args );
    }
}