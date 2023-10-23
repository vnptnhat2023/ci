<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql;

use \Closure;

use Red2Horse\Exception\
{
    ErrorArrayException,
    ErrorParameterException,
    ErrorPropertyException
};

use Red2Horse\Mixins\Classes\Sql\Model;
use Red2Horse\Facade\Query\QueryFacadeInterface;
use Red2Horse\Mixins\Classes\Data\DataArrayEventsClass;
use Red2Horse\Mixins\Classes\Sql\Builder\SqlCompiler;
use Red2Horse\Mixins\Interfaces\Sql\BaseBuilderInterface;
use Red2Horse\Mixins\Traits\Object\TraitInstanceTrigger;
use Red2Horse\Mixins\Traits\Object\TraitReadOnly;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;


use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Data\
{
    dataKeyAssocInstance,
    dataKeyInstance
};
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Model\BaseModel;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class BaseBuilder implements BaseBuilderInterface
{
    use TraitSingleton, TraitInstanceTrigger, TraitReadOnly;

    public string $table;

    public int $updateLimitRows = 1000;
    public int $deleteLimitRows = 1000;
    public int $getLimitRows = 1000;

    private array $select = [];
    private array $distinct = [];
    private array $update = [];
    private array $delete = [];
    private array $insert = [];
    private array $from = [];
    private array $andWhere = [];
    private array $orWhere = [];
    private array $where = [];
    private array $join = [];
    private array $set = [];
    private array $limit = [];
    private array $andOn = [];
    private array $orOn = [];
    private array $on = [];
    private array $in = [];
    private array $orderBy = [];
    private array $get = [];

    private QueryFacadeInterface $connection;

    private \stdClass $modelProperty;

    private string $compilerNamespace;

    private array $lastQueryString = [];

    public function __construct ( ?string $sqlCompilerClassNamespace = null ) 
    {
        // dd( Model::$modelNamespace );
        $this->compilerNamespace = $sqlCompilerClassNamespace ?: SqlCompiler::class;
        
        // dd( $this->__toStdClass() );
    }

    public function getConnection () : QueryFacadeInterface
    {
        return $this->connection;
    }

    public function setConnection ( ?QueryFacadeInterface $connection = null ) : void
    {
        $this->connection = ( null === $connection )
            ? getComponents( 'query' ) 
            : $connection;
    }

    public function setModelProperty ( ?\stdClass $childModel = null ) : void
    {
        $this->modelProperty = $childModel;
    }

    /** @return mixed */
    private function query ()
    {
        if ( ! isset( $this->table ) )
        {
            throw new ErrorPropertyException( 'Property : "table" is not defined' );
        }

        $lastQuery = $this->getLastQueryString();

        if ( getConfig( 'sql' )->useQuery )
        {
            return $this->getConnection()->query( $lastQuery );
        }

        return $lastQuery;
    }

    /** @return array|string */
    private function fetchArray ()
    {
        $lastQuery = $this->getLastQueryString();

        if ( getConfig( 'sql' )->useQuery )
        {
            return $this->getConnection()->resultArray( $lastQuery );
        }

        return $lastQuery;
    }

    public function getLastQueryString () : string
    {
        if ( ! empty( $this->lastQueryString ) )
        {
            $lastSql = $this->lastQueryString[ array_key_last( $this->lastQueryString )] . ';';
            return $lastSql;
        }

        return '';
    }

    public function getCompilerProperties () : SqlCompiler
    {
        /** @var SqlCompiler $compilerInstance */
        $compilerInstance = getInstance( $this->compilerNamespace );
        return $compilerInstance->init( $this->__toStdClass() );
    }

    public function select ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $this->_afterCall( $callable, $dataNonAssoc );

        $this->select[] = $dataNonAssoc->keyMap( $data );
        return $this;
    }

    public function distinct ( array $data, ?Closure $callable = null, int $len = 100  ) : self
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $this->_afterCall( $callable, $dataNonAssoc );

        $this->distinct[] = $dataNonAssoc->keyMap( $data );
        return $this;
    }

    public function get ()
    {
        $sql = $this->getCompilerProperties()->get();
        $this->get[] = $sql;
        $this->lastQueryString[] = $sql;

        return $this->fetchArray();
    }

    public function update ( int $len = 1 )
    {
        $sql = $this->getCompilerProperties()->update( $len );
        $this->update[] = $sql;
        $this->lastQueryString[] = $sql;

        return $this->query();
    }

    public function delete ( $len = 1/*, ?Closure $callable = null*/ )
    {
        $sql = $this->getCompilerProperties()->delete( $len/*, $callable */ );
        $this->delete[] = $sql;
        $this->lastQueryString[] = $sql;

        return $this->query();
    }

    /** @TODO ON DUPLICATE KEY UPDATE */
    public function insert ( array $data, ?Closure $callable = null, int $len = 100 )
    {
        $this->_beforeCall( $data, $len );
        $columns = array_keys( $data );

        $this->_trigger( null, [ '_allowedFieldsFilter' ], $columns );

        /** @var array $data */
        [ '_timeFormatter' => $data ] = $this->_trigger( null, [ '_timeFormatter' ], $data );

        $dataAssoc = dataKeyAssocInstance();
        $this->_afterCall( $callable, $dataAssoc );

        $data = $dataAssoc( $data, false );

        /** Compile */
        $sql = $this->getCompilerProperties()->insert( $data );
        
        $this->insert[] = $sql;
        $this->lastQueryString[] = $sql;
        
        return $this->query();
    }

    public function fetch ( array $where = [], array $orderBy = [], ?Closure $callableWhere = null, ?Closure $callableOrderBy = null )
    {
        empty( $where )   || $this->where( $where, $callableWhere );
        empty( $orderBy ) || $this->orderBy( $orderBy, $callableOrderBy );

        return $this->get();
    }

    public function fetchFirst ( array $where = [], array $orderBy = [], bool $asObject = false, ?Closure $callableWhere = null, ?Closure $callableOrderBy = null )
    {
        empty( $where )   || $this->where( $where, $callableWhere );
        empty( $orderBy ) || $this->orderBy( $orderBy, $callableOrderBy );

        $data = ( array ) $this->limit( 1 )->get();
        
        if ( ! array_key_exists( 0, $data ) )
        {
            throw new ErrorArrayException;
        }

        return $asObject ? ( object ) $data[ 0 ] : $data[ 0 ];
    }

    public function edit ( array $set, array $where, int $len = 1, ?Closure $setCallable = null, ?Closure $whereCallable = null)
    {
        return $this 
            ->set( $set, $setCallable ) 
            ->where( $where, $whereCallable ) 
            ->update( $len );
    }

    public function remove ( array $where = [], array $in = [], int $len = 1, ?Closure $callableWhere = null, ?Closure $callableIn = null )
    {
        ! empty( $where)    || $this->where( $where, $callableWhere );
        ! empty( $in )      || $this->in( $in, $callableIn );

        return $this->delete( $len );
    }

    public function add ( array $data, ?Closure $callable = null, int $len = 100 )
    {
        return $this->insert( $data, $callable, $len );
    }

    public function from ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->_beforeCall( $data, $len );

        if ( getComponents( 'common' )->isAssocArray( $data ) )
        {
            $dataAssoc = dataKeyAssocInstance();
            $dataAssoc->valueDelimiter = '`';
            $dataAssoc->operator = ' ';
            $this->_afterCall( $callable, $dataAssoc );

            $data = $dataAssoc( $data );
        }
        else
        {
            getInstance( DataArrayEventsClass::class )->setFor( 'from' );
            $dataNonAssoc = dataKeyInstance();
            $this->_afterCall( $callable, $dataNonAssoc );
            $data = $dataNonAssoc->keyMap( $data );
        }

        $this->from[] = $data;
        return $this;
    }

    /** @return array|string */
    private function _where ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null )
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorParameterException( 'Invalid format property: data' );
        }

        $this->_beforeCall( $data, $len );
        $dataAssoc = dataKeyAssocInstance();

        if ( null !== $type )
        {
            $dataAssoc->toStringSepChar = " {$type} ";
        }

        $this->_afterCall( $callable, $dataAssoc );

        return $dataAssoc( $data );
    }

    public function andWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->andWhere[] = $this->_where( $data, $callable, $len, 'AND' );
        return $this;
    }

    public function orWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->orWhere[] = $this->_where( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function where ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->where[] = $this->_where( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function join (
        array $data, 
        ?array $on = null, 
        ?string $onType = null, 
        ?Closure $callableJoin = null, 
        ?Closure $callableJOn = null, 
        int $len = 100 ) : self
    {
        $this->_beforeCall( $data, $len );

        if ( getComponents( 'common' )->isAssocArray( $data ) )
        {
            $dataAssoc = dataKeyAssocInstance();
            $dataAssoc->valueDelimiter = '`';
            $dataAssoc->operator = ' ';
            $this->_afterCall( $callableJoin, $dataAssoc );

            $data = $dataAssoc( $data );
        }
        else
        {
            getInstance( DataArrayEventsClass::class )->setFor( 'join' );
            $dataNonAssoc = dataKeyInstance();
            $dataNonAssoc->toStringSepChar = ' ';
            $this->_afterCall( $callableJoin, $dataNonAssoc );

            $data = $dataNonAssoc->keyMap( $data );
        }

        $this->join[] = $data;

        if ( null !== $on )
        {
            $onType = null !== $onType ? strtolower( $onType ) : '';

            if ( $onType == 'or' )
                $this->orOn( $on, $callableJOn );
            else if ( $onType == 'and' )
                $this->andOn( $on, $callableJOn );
            else
                $this->on( $on, $callableJOn );
        }

        return $this;
    }

    public function set ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->_beforeCall( $data, $len );
        
        /** Trigger _allowedFieldsFilter */
        $this->_trigger( null, [ '_allowedFieldsFilter' ], $data );

        /**
         * Trigger _timeFormatter
         * @var array $data
         */
        [ '_timeFormatter' => $data ] = $this->_trigger( null, [ '_timeFormatter' ], $data );

        $dataAssoc = dataKeyAssocInstance();
        $this->_afterCall( $callable, $dataAssoc );

        $this->set[] = $dataAssoc( $data );

        return $this;
    }

    public function limit ( int $before = 0, int $after = 0/*, ?Closure $callable = null*/ ) : self
    {
        $before = $before > 0 ? $before : '';
        $after  = $after  > 0 ? ", $after" : '';
        $str = sprintf( '%s%s', $before, $after );

        if ( '' !== $str )
        { // $str = $this->_afterCall( $str, $callable );
            $this->limit[] = $str;
        }

        return $this;
    }

    public function is ()
    {
        $char = 'IS';
    }

    public function not ()
    {
        $char = 'NOT';
    }

    public function null ()
    {
        $char = 'NULL';
    }

    private function _on ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null ) : string
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataAssoc = dataKeyAssocInstance();
        $dataAssoc->valueDelimiter = '`';

        if ( null !== $type )
        {
            $type = trim( $type );
            $dataAssoc->toStringSepChar = " {$type} ";
        }

        $this->_afterCall( $callable, $dataAssoc );

        return $dataAssoc( $data );
    }

    public function andOn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->andOn[] = $this->_on( $data, $callable, $len, 'AND' );
        return $this;
    }

    public function orOn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->orOn[] = $this->_on( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function on ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->on[] = $this->_on( $data, $callable, $len );
        return $this;
    }

    public function in ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $dataNonAssoc->keyDelimiter = '\'';
        $this->_afterCall( $callable, $dataNonAssoc );

        $this->in[] = $dataNonAssoc->keyMap( $data );
        return $this;
    }

    public function orderBy ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );

        /** Validate Data */
        $mapFn = function ( string $str ) : bool {
            return ! in_array( strtoupper( $str ), [ 'ASC', 'DESC' ] );
        };
        if ( [] !== array_filter( $data, $mapFn ) )
        {
            $error = 'Values of $data variable must be in: [ DESC, ASC ].';
            throw new ErrorArrayException( $error );
        }
        /** End validate Data */

        $dataAssoc = dataKeyAssocInstance();
        $dataAssoc->valueDelimiter = '';
        $dataAssoc->operator = ' ';
        $this->_afterCall( $callable, $dataAssoc );

        $this->orderBy[] = $dataAssoc( $data );
        return $this;
    }

    /** @throws ErrorArrayException */
    private function _beforeCall ( array $data, int $len ) : void
    {
        if ( $count = count( $data ) > $len )
        {
            throw new ErrorParameterException( sprintf( 'Argument 1 length: %s than: %s', $count, $len ) );
        }
    }

    private function _afterCall ( ?Closure $callable = null, ?object &$dataFilter = null ) :void
    {
        if ( is_callable( $callable ) )
        {
            call_user_func( $callable, $dataFilter );
        }
    }

    private function _getTimesFormatter ( ) : array
    {
        $data = [];
        
        empty( $this->modelProperty->createdAt ) || $data[] = $this->modelProperty->createdAt;
        empty( $this->modelProperty->updatedAt ) || $data[] = $this->modelProperty->updatedAt;
        empty( $this->modelProperty->deletedAt ) || $data[] = $this->modelProperty->deletedAt;

        return $data;
    }

    /**
     * @param array $rawData (Non-)Associative
     * @throws ErrorArrayException
     */
    private function _allowedFieldsFilter ( array $rawData ) : bool
    {
        $common = getComponents( 'common' );
        
        if ( $common->isAssocArray( $rawData ) )
        {
            $rawData = array_keys( $rawData );
        }

        if ( [] === $rawData || [] === $this->modelProperty->allowedFields )
        {
            return false;
        }
        else if ( $this->modelProperty->allowedFields === $rawData )
        {
            return true;
        }

        if ( ! $res = $common->arrayInArray( $this->modelProperty->allowedFields, $rawData ) )
        {
            throw new ErrorArrayException( 'Some fields is not allowed in "column" variable' );
        }

        return $res;
    }

    /**
     * @param array $data Associative
     * @param array $timeProperty Associative
     */
    private function _timeFormatter ( array $data ) : array
    {
        if ( [] === $this->_getTimesFormatter() )
        {
            return $data;
        }

        $mapFn = function ( array $prop ) use ( $data ) : array
        {
            if ( $this->_allowedFieldsFilter( $prop ) )
            {
                $key = array_key_first( $prop );

                if ( ! in_array( $prop[ $key ], $this->modelProperty->validTimeFormat ) )
                {
                    throw new ErrorParameterException( sprintf( 'Invalid parameter: "%s"', $key ) );
                }

                $data[ $key ] = date( $prop[ $key ] );
                return $data;
            }

            return $prop;
        };

        $res = array_map( $mapFn, $this->_getTimesFormatter() );
        return reset( $res );
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
        // getInstance
        // return BaseModel()->$method( ...$args );
    }
}