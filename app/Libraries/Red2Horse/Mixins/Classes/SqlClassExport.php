<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes;

use Red2Horse\Facade\Auth\Message;
use Red2Horse\
{
    Mixins\Traits\TraitSingleton
};

use function Red2Horse\Mixins\Functions\
{
    getTable,
    getColumn,
    getComponents,
    getConfig,
    getField,
    getFields,
    getHashPass,
    getInstance,
    setSuccessMessage,
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

/** @todo escape */
class SqlClassExport
{
    use TraitSingleton;

    private function __construct () { }

    /**
     * Seeder
     * Validate, query with intersect array
     * @return array [ intersect ] | [ intersect, sql ]
     */
    public function seed ( string $tableName, array $intersect = [], bool $query = false ) : array
    {
        $validation = getComponents( 'validation' );
        $req = getComponents( 'request' );
        $keys = getFields( $intersect, $tableName, true, false );

        $return = [ 'intersect' => $intersect, 'sql' => '' ];

        if ( empty( $posts = $req->post() ) ) { return $return; }

        if ( ! empty( $intersect ) )
        {
            $posts = $req->post( $intersect );
            $rules = $validation->getRules( $intersect );
        }
        else
        {
            $rules = $validation->getRules( $keys );
        }

        if ( ! $validation->isValid( $posts, $rules ) )
        {
            getInstance( Message::class )::$errors += $validation->getErrors();
            return $return;
        }

        $user_password = getField( 'password', getTable( 'user' ) );
        if ( array_key_exists( $user_password, $posts ) )
        {
            $posts[ $user_password ] = getHashPass( $posts[ $user_password ] );
        }

        $sql = $this->seedExport( $tableName, $posts );

        if ( $query && ! getComponents( 'user' )->querySimple( $sql ) )
        {
            throw new \Error( sprintf( 'Cannot query %s:%s', __METHOD__, __LINE__ ) );
        }

        setSuccessMessage( ( array ) getComponents( 'common' )->lang( 'Red2Horse.successSeeder' ) );

        $return[ 'sql' ] = $sql;
        return $return;
    }

    public function seedExport ( string $tableName, array $data ) : string
    {
        if ( empty( $tableName ) || empty( $data ) )
        {
            $errorStr = sprintf( 'Invalid parameters format. %s:%s:%s', __FILE__, __METHOD__, __LINE__ );
            throw new \Error( sprintf( $errorStr ), 406 );
        }

        $common = getComponents( 'common' );

        $escColumns = function ( string $str ) use( $common ) {
            $str = $common->esc( $str );
            return "`{$str}`";
        };

        $escValue = function ( string $str ) use( $common ) {
            $str = $common->esc( $str );
            return "'{$str}'";
        };

        $columns = implode( ',', array_map( $escColumns , array_keys( $data ) ) );
        $values = implode( ',', array_map( $escValue , array_values( $data ) ) );

        $sql = sprintf(
            'INSERT INTO `%s`(%s) VALUES(%s);',
            getTable( $tableName ),
            $columns,
            $values
        );

        return $sql;
    }

    /**
     * @param bool $query true: ( string + query ); false ( string ) only
     */
    public function createTable ( string $tableName, bool $query = false ) : string
    {
        $tableName = getTable( $tableName );
        $tableKeyName = getTable( $tableName, true );
        $columns = getColumn( $tableName );

        $varsFn = fn ( $val ) => is_array( $val ) && array_key_exists( 0, $val ) ? $val[ 0 ] : $val;
        $vars = array_map( $varsFn, $columns );
        $vars[ $tableKeyName ] = $tableName;

        $tableVarName = sprintf( '%sTemplateTbl', getComponents( 'common' )->camelCase( $tableKeyName ) );
        $tableTemplate = getConfig( 'sql' )->{ $tableVarName };

        $match = function( $match ) use ( $vars ) { return $vars[ $match[ 1 ] ]; };
        $sqlParser = preg_replace_callback( '/:(.*?):/', $match, $tableTemplate );

        if ( $query )
        {
            if ( ! getComponents( 'user' )->querySimple( $sqlParser ) )
            {
                throw new \Error( sprintf( 'Cannot query %s:%s', __METHOD__, __LINE__  ), 406 );
            }
        }

        setSuccessMessage( ( array ) getComponents( 'common' )->lang( 'Red2Horse.success', [ 'Created' ] ) );

        return $sqlParser;
    }

    public function selectExport ( string $tbl, array $data ) : string
    {
        $arrayKeyMap = new arrayKeyMap;
        return $arrayKeyMap( $tbl, $data );
    }

    /**
     * @throws \Error
     * @param \stdClass|array $data
     */
    public function selectExports ( array $data ) : string
    {
        $data = $this->_selectExportFormat( $data );

        if ( empty( $tables = $data[ 'tables' ] ) )
        {
            throw new \Error( 'Invalid format data variable.', 406 );
        }

        $fields = [];

        foreach ( $tables as $tbl )
        {
            if ( empty( $field = $data[ 'data' ][ $tbl ]  ) )
            {
                throw new \Error( 'Invalid format data variable.', 406 );
            }
            /** @var string[] $fields */
            $fields[] = $this->selectExport( $tbl, $field );
        }
        
        return implode( ',', $fields );
    }

    private function _selectExportFormat ( array $data ) : array
    {
        $dataTables = [];
        $dataColumns = [];

        foreach ( $data as $table => $columns )
        {
            $dataTables[] = getTable( $table );

            if ( empty( $columns ) )
            {
                $dataColumns[ $table ] = getColumn( $table );
            }
            else if ( is_array( $columns ) )
            {
                foreach ( $columns as $columnElement )
                {
                    if ( is_array( $columnElement ) )
                    {
                        $columnElement[ 0 ] = getField( $columnElement[ 0 ], $table );
                        $dataColumns[ $table ][ $columnElement[ 0 ] ] = $columnElement;
                    }
                    else
                    {
                        $dataColumns[ $table ][ $columnElement ] = getField( $columnElement, $table );
                    }
                }
            }
            else
            {
                $dataColumns[ $table ] = getFields( $columns, $table, false );
            }
        }

        $data = [
            'tables' => $dataTables,
            'data' => $dataColumns
        ];

        return $data;
    }
}

/**
 * Assign string before elements array.
 */
class arrayKeyMap{
    public string $tbl;
    public int $limit = 10;
    public array $map;

    private bool $mapped = false;

    /**
     * @throws \Error
     * @return array|string
     */
    public function __invoke( string $tbl, array $map, bool $toString = true )
    {
        if ( empty( $map ) || '' === $tbl )
        {
            throw new \Error( 'The property $map cannot empty.', 406);
        }
        $this->tbl = $tbl;
        $this->map = $map;

        $mapFunc = function ( $value )
        {
            if ( is_array( $value ) )
            {
                $value = implode( ' ', $value );
            }

            return sprintf( '%s.%s', $this->tbl, $value );
        };

        $this->map = array_map( $mapFunc, $this->map );
        
        $this->mapped = true;
        $return = $toString ? $this->__toString() : $this->map;
        $this->mapped = false;

        return $return;
    }

    public function __toString () : string
    { 
        $this->mapped || $this->__invoke( $this->tbl, $this->map, false );
        return implode( ',', $this->map );
    }
};