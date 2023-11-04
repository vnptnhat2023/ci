<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql;

use Red2Horse\Exception\ErrorPropertyException;
use Red2Horse\Facade\Query\QueryFacadeInterface as connection;
use Red2Horse\Mixins\Interfaces\Sql\ModelInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Sql\getTable;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Model implements ModelInterface
{
    use TraitSingleton;

    protected           BaseBuilder             $builder;
    /** @var            <string, string>        $createdAt */
    public              array                   $createdAt;
    /** @var            <string, string>        $deletedAt */
    public              array                   $deletedAt;
    /** @var            <string, string>        $updatedAt */
    public              array                   $updatedAt;
    /** @var            string[]                $updatedAt */
    public              array                   $validTimeFormat = [ 'Y-m-d H:i:s', 'Y-m-d' ];
    /** @var            string[]                $allowedFields */
    public              array                   $allowedFields  = [];
    protected           string                  $table;
    private             bool                    $init           = false;
    protected           bool                    $useSoftDelete  = false;

    public function __construct () {}

    public static function model (
        ?string $table = null, ?connection $connection = null, \stdClass $childProperties = null, bool $getShare = true
    ) : self
    {
        return $getShare
            ? getInstance( self::class )->init( $table, $connection, $childProperties )
            : ( new self )->init( $table, $connection, $childProperties );
    }

    public function init ( ?string $table = null, ?connection $connection = null, \stdClass $childProperties = null ) : self
    {
        $this->builder = new BaseBuilder;

        $this
            ->setTable( $table )
            ->setConnection( $connection )
            ->setModelProperty( $childProperties );

        $this->builder->init();
        $this->init = true;

        return $this;
    }

    public function getInit ()
    {
        return $this->init;
    }

    public function setTable ( ?string $table = null ) : self
    {
        if ( null === $table &&  ! isset( $this->table ) )
        {
            throw new ErrorPropertyException( 'Property: "table" not found' );
        }

        $table = getTable( $table ?? $this->table, false, false, true );
        $this->builder->table = $table;

        return $this;
    }

    public function setConnection ( ?connection $connection = null ) : self
    {
        $this->builder->setConnection( $connection );
        return $this;
    }
    
    public function setModelProperty ( ?\stdClass $childProperties = null ) : self
    {
        $childProperties = $childProperties ?: $this->__toStdClass();
        if ( isset( $childProperties->builder ) )
        {
            unset( $childProperties->builder );
        }

        $this->builder->setModelProperty( $childProperties );
        return $this;
    }

    public function toggleAllowedFields ( array $allowedFields ) : self
    {
        $this->builder->beforeAllowedFieldsFilter( $allowedFields );
        return $this;
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