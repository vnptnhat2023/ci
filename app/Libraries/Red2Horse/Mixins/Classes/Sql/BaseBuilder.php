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

use Red2Horse\Facade\Query\QueryFacadeInterface;
use Red2Horse\Mixins\Classes\Data\DataArrayEventsClass;
use Red2Horse\Mixins\Classes\Sql\Builder\SqlBuilderData;
use Red2Horse\Mixins\Classes\Sql\Builder\SqlCompiler;
use Red2Horse\Mixins\Interfaces\Sql\BaseBuilderInterface;
use Red2Horse\Mixins\Traits\Object\TraitInstanceTrigger;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Data\
{
    dataKeyAssocInstance,
    dataKeyInstance
};

use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Instance\getInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class BaseBuilder implements BaseBuilderInterface
{
    use TraitSingleton, TraitInstanceTrigger;

    public      string                          $table;
    private     QueryFacadeInterface            $connection;
    private     \stdClass                       $modelProperty;
    private     SqlCompiler                     $compiler;
    private     SqlBuilderData                  $data;
    private     array                           $lastQueryString  = [];

    private     array                           $allowedFieldsFilters = [
        'beforeAllowedFieldsFilter',
        '_allowedFieldsFilter',
        '_afterAllowedFieldsFilter'
    ];
    /** Temporary Allowed-fields */
    private     array                           $toggleAllowedFields = [];

    public function __construct ()
    {
        helpers( [ 'array_data' ] );
    }

    public function init(  ?string $compilerNamespace = null, ?string $dataNamespace = null  )
    {
        $compilerNS = $compilerNamespace    ?: SqlCompiler::class;
        $dataNS     = $dataNamespace        ?: SqlBuilderData::class;

        $this->compiler  = getInstance( $compilerNS );
        $this->data      = getInstance( $dataNS );

        $this->compiler->init( $this->table );

        return $this;
    }

    public function getConnection () : QueryFacadeInterface
    {
        return $this->connection;
    }

    public function setConnection ( ?QueryFacadeInterface $connection = null ) : void
    {
        $this->connection = null === $connection ? getComponents( 'query' ) : $connection;
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

    public function select ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $this->_afterCall( $callable, $dataNonAssoc );

        $this->data->select[] = $dataNonAssoc->keyMap( $data );

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

        $this->data->distinct[] = $dataNonAssoc->keyMap( $data );
        return $this;
    }

    public function get ()
    {
        if ( [] === $this->data->limit )
        {
            $this->limit( $this->data->getLimitRows );
        }

        $sql = $this->compiler->get();
        $this->lastQueryString[] = $sql;

        $query = $this->fetchArray();
        $this->data->reset();

        return $query;
    }

    public function update ( int $len = 1 )
    {
        $sql = $this->compiler->update( $len );
        $this->lastQueryString[] = $sql;

        $query = $this->query();
        $this->data->reset();

        return $query;
    }

    public function delete ( $len = 1/*, ?Closure $callable = null*/ )
    {
        $sql = $this->compiler->delete( $len/*, $callable */ );
        $this->lastQueryString[] = $sql;

        $query = $this->query();
        $this->data->reset();

        return $query;
    }

    /** @TODO ON DUPLICATE KEY UPDATE */
    public function insert ( array $data, ?Closure $callable = null, bool $replace = false, int $len = 100 )
    {
        $this->_beforeCall( $data, $len );
        $columns = array_keys( $data );

        $this->_trigger( null, $this->allowedFieldsFilters, $columns );

        /** @var array $data */
        [ '_timeFormatter' => $data ] = $this->_trigger( null, [ '_timeFormatter' ], $data );

        $dataAssoc = dataKeyAssocInstance();
        $this->_afterCall( $callable, $dataAssoc );

        $data = $dataAssoc( $data, false );

        /** Compile */
        $sql = $this->compiler->insert( $data, $replace );
        
        $this->data->insert[] = $sql;
        $this->lastQueryString[] = $sql;
        
        $query = $this->query();
        $this->data->reset();
        
        return $query;
    }

    public function replace ( array $data, ?Closure $callable = null, int $len = 100 )
    {
        $this->_beforeCall( $data, $len );
        $columns = array_keys( $data );

        $this->_trigger( null, $this->allowedFieldsFilters, $columns );

        /** @var array $data */
        [ '_timeFormatter' => $data ] = $this->_trigger( null, [ '_timeFormatter' ], $data );

        $dataNonAssoc = dataKeyInstance();
        $dataNonAssoc->keyDelimiter = '\'';
        $this->_afterCall( $callable, $dataNonAssoc );

        $data = $dataNonAssoc->keyMap( $data, false );

        /** Compile */
        $sql = $this->compiler->replace( $data );
        $this->lastQueryString[]    = $sql;
        
        $query = $this->query();
        $this->data->reset();

        return $query;
    }

    public function fetch ( array $where = [], array $orderBy = [], ?Closure $callableWhere = null, ?Closure $callableOrderBy = null ) : array
    {
        empty( $where )   || $this->where( $where, $callableWhere );
        empty( $orderBy ) || $this->orderBy( $orderBy, $callableOrderBy );

        return ( array ) $this->get();
    }

    /**
     * @throws ErrorArrayException
     * @return array|object
     */
    public function fetchFirst ( array $where = [], array $orderBy = [], ?Closure $callableWhere = null, ?Closure $callableOrderBy = null )
    {
        empty( $where )   || $this->where( $where, $callableWhere );
        empty( $orderBy ) || $this->orderBy( $orderBy, $callableOrderBy );

        $data = ( array ) $this->limit( 1 )->get();

        if ( ! array_key_exists( 0, $data ) )
        {
            return $data;
        }

        return $data[ 0 ];
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

    public function add ( array $data, ?Closure $callable = null, bool $replace = false, int $len = 100 )
    {
        return $this->insert( $data, $callable, $replace, $len );
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

        $this->data->from[] = $data;
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
        $dataAssoc->operator = ' = ';

        if ( null !== $type )
        {
            $dataAssoc->toStringSepChar = " {$type} ";
        }

        $this->_afterCall( $callable, $dataAssoc );

        return $dataAssoc( $data );
    }

    // Not: `a.b` != `d.e`
    public function andWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->andWhere[] = $this->_where( $data, $callable, $len, 'AND' );
        return $this;
    }

    // Not: `a.b` != `d.e`
    public function orWhere ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->orWhere[] = $this->_where( $data, $callable, $len, 'OR' );
        return $this;
    }

    // (not)(whereIn, orWhereIn, andWhereIn)
    // (not)(whereNull, orWhereNull, andWhereNull)
    // whereLike, orWhereLike, andWhereLike,
    public function where ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->where[] = $this->_where( $data, $callable, $len, 'OR' );
        return $this;
    }

    /** @return array|string */
    private function _like ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null )
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorParameterException( 'Invalid format property: data' );
        }

        $this->_beforeCall( $data, $len );

        $dataAssoc = dataKeyAssocInstance();
        $dataAssoc->escapeChar          = '\%';
        $dataAssoc->operator            = ' LIKE ';

        if ( null !== $type )
        {
            $dataAssoc->toStringSepChar = " {$type} ";
        }

        $this->_afterCall( $callable, $dataAssoc );

        return $dataAssoc( $data );
    }

    public function andLike ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->andLike[] = $this->_like( $data, $callable, $len, 'AND' );
        return $this;
    }

    public function orLike ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->orLike[] = $this->_like( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function like ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->like[] = $this->_like( $data, $callable, $len, 'OR' );
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

        $this->data->join[] = $data;

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
        $this->_trigger( null, $this->allowedFieldsFilters, $data );

        /**
         * Trigger _timeFormatter
         * @var array $data
         */
        [ '_timeFormatter' => $data ] = $this->_trigger( null, [ '_timeFormatter' ], $data );

        $dataAssoc = dataKeyAssocInstance();
        $this->_afterCall( $callable, $dataAssoc );

        $this->data->set[] = $dataAssoc( $data );

        return $this;
    }

    public function limit ( int $before = 0, int $after = 0/*, ?Closure $callable = null*/ ) : self
    {
        $before = $before > 0 ? $before : '';
        $after  = $after  > 0 ? ", $after" : '';
        $str = sprintf( '%s%s', $before, $after );

        if ( '' !== $str )
        { // $str = $this->_afterCall( $str, $callable );
            $this->data->limit[] = $str;
        }

        return $this;
    }

    private function _on ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null ) : string
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataAssoc = dataKeyAssocInstance();
        $dataAssoc->operator = ' = ';
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
        $this->data->andOn[] = $this->_on( $data, $callable, $len, 'AND' );
        return $this;
    }

    public function orOn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->orOn[] = $this->_on( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function on ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->on[] = $this->_on( $data, $callable, $len, 'OR' );
        return $this;
    }

    /** @param array $data Associative */
    private function _in ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null, bool $not = false ) : string
    {
        if ( ! getComponents( 'common' )->isAssocArray( $data ) )
        {
            throw new ErrorParameterException( 'Parameter 1: "data" not an associative array' );
        }

        $this->_beforeCall( $data, $len );

        $dataAssoc                      = dataKeyAssocInstance();
        $dataAssoc->keyDelimiter        = '`';
        $dataAssoc->valueDelimiter      = '';
        $dataAssoc->escapeValues        = false;
        $dataAssoc->operator            = $not ? ' NOT IN' : ' IN';

        $noExplodeArray = [];
        foreach( $data as $value )
        {
            foreach( $value as $valueInside )
            {
                $noExplodeArray[] = ( string ) $valueInside;
            }
        }

        $dataAssoc->setNoExplode( 'v', $noExplodeArray );

        if ( null !== $type )
        {
            $type = trim( $type );
            $dataAssoc->toStringSepChar = " {$type} ";
        }
        else
        {
            $dataAssoc->toStringSepChar = " OR ";
        }

        $this->_afterCall( $callable, $dataAssoc );

        return $dataAssoc( $data );
    }

    public function in ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->in[] = $this->_in( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function notIn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->notIn[] = $this->_in( $data, $callable, $len, 'OR', true );
        return $this;
    }

    public function orIn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->orIn[] = $this->_in( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function orNotIn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->orNotIn[] = $this->_in( $data, $callable, $len, 'OR', true );
        return $this;
    }

    public function andIn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->andIn[] = $this->_in( $data, $callable, $len, 'AND' );
        return $this;
    }

    public function andNotIn ( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->andNotIn[] = $this->_in( $data, $callable, $len, 'AND', TRUE );
        return $this;
    }

    public function _null ( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null, bool $not = false ) : string
    {
        if ( ! getComponents( 'common' )->nonAssocArray( $data ) )
        {
            throw new ErrorArrayException;
        }

        $this->_beforeCall( $data, $len );
        $dataNonAssoc = dataKeyInstance();
        $dataNonAssoc->keyDelimiter = '`';
        $dataNonAssoc->format = '%2$s %1$s';

        // WHERE `A` IS NULL OR `B` IS NOT NULL
        if ( null !== $type )
        {
            $type = trim( $type );
            $dataNonAssoc->toStringSepChar = " {$type} ";
        }

        $this->_afterCall( $callable, $dataNonAssoc );
        $isNotNull = $not ? 'IS NOT NULL' : 'IS NULL';

        return $dataNonAssoc( $isNotNull, $data, true );
    }

    public function null( array $data, ?Closure $callable = null, int $len = 100, ?string $type = null ) : self
    {
        $this->data->null[] = $this->_null( $data, $callable, $len, 'OR' );
        return $this;
    }

    public function notNull( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->notNull[] = $this->_null( $data, $callable, $len, 'OR', true );
        return $this;
    }

    public function orNull( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->orNull[] = $this->_null( $data, $callable, $len, 'OR' );
        return $this;
    }
    public function orNotNull( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->orNotNull[] = $this->_null( $data, $callable, $len, 'OR', true );
        return $this;
    }
    public function andNull( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->andNull[] = $this->_null( $data, $callable, $len, 'AND' );
        return $this;
    }
    public function andNotNull( array $data, ?Closure $callable = null, int $len = 100 ) : self
    {
        $this->data->andNotNull[] = $this->_null( $data, $callable, $len, 'AND', true );
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

        $this->data->orderBy[] = $dataAssoc( $data );
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

    private function _afterCall ( ?Closure $callable = null, ?object &$dataFilter = null ) : void
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

    /** @param array $allowedFields (Non-)Associative */
    public function beforeAllowedFieldsFilter ( array $allowedFields ) : void
    {
        if ( getComponents( 'common' )->isAssocArray( $allowedFields ) )
        {
            $allowedFields = array_keys( $allowedFields );
        }

        $this->toggleAllowedFields = $allowedFields;
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

        if ( [] === $this->modelProperty->allowedFields )
        {
            return false;
        }
        else if ( $this->modelProperty->allowedFields === $rawData )
        {
            return true;
        }

        $rawDataFields = implode( ', ', $rawData );

        if ( $common->arrayInArray( $this->toggleAllowedFields, $rawData ) )
        {
            return true;
        }

        if ( ! $common->arrayInArray( $this->modelProperty->allowedFields, $rawData ) )
        {
            $errorException = sprintf( 
                '"Column" variable need in list of allowed fields: "%s", given: "%s"', 
                implode( ', ', $this->modelProperty->allowedFields ), 
                $rawDataFields
            );
            throw new ErrorArrayException( $errorException );
        }

        return true;
    }

    private function _afterAllowedFieldsFilter () : void
    {
        $this->toggleAllowedFields = [];
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

        $mapFn = function ( array $prop ) : array
        {
            if ( $this->_allowedFieldsFilter( $prop ) )
            {
                $key = array_key_first( $prop );

                if ( ! in_array( $prop[ $key ], $this->modelProperty->validTimeFormat ) )
                {
                    throw new ErrorParameterException( sprintf( 'Invalid parameter: "%s"', $key ) );
                }

                $prop[ $key ] = date( $prop[ $key ] );
            }

            return $prop;
        };

        $res = array_map( $mapFn, $this->_getTimesFormatter() );
        return array_merge( $data, ...$res );
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
}