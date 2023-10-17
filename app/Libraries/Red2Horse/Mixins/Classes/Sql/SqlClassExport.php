<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql;

use Red2Horse\Exception\
{
    ErrorSqlException,
    ErrorArrayException,
    ErrorParameterException
};

use Red2Horse\Mixins\Classes\Data\
{
    DataKeyMap,
    DataAssocKeyMap
};

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setSuccessMessage;
use function Red2Horse\Mixins\Functions\Password\getHashPass;
use function Red2Horse\Mixins\Functions\Sql\getColumn;
use function Red2Horse\Mixins\Functions\Sql\getField;
use function Red2Horse\Mixins\Functions\Sql\getFields;
use function Red2Horse\Mixins\Functions\Sql\getTable;

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
     * @throws ErrorSqlException
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
            setErrorMessage( $validation->getErrors() );
            return $return;
        }

        $posts = $this->formData( $posts );
        $sql = $this->seedExport( $tableName, $posts );

        if ( $query && ! getComponents( 'user' )->querySimple( $sql ) )
        {
            throw new ErrorSqlException( $sql );
        }

        setSuccessMessage();

        $return[ 'sql' ] = $sql;
        return $return;
    }

    private function formData ( array $posts ) : array
    {
        $user_password = getField( 'password', getTable( 'user' ) );
        if ( array_key_exists( $user_password, $posts ) )
        {
            $posts[ $user_password ] = getHashPass( $posts[ $user_password ] );
        }

        $user_role = getField( 'role', getTable( 'user_group' ) );
        if ( array_key_exists( $user_role, $posts ) )
        {
            $posts[ $user_role ] = json_encode( [ 'role' => $posts[ $user_role ], 'hash' => '' ] );
        }

        $user_permission = getField( 'permission', getTable( 'user_group' ) );
        if ( array_key_exists( $user_permission, $posts ) )
        {
            $mapFn = fn( $str ) => trim( $str );
            $explode = explode( ',', str_replace( '  ', '', $posts[ $user_permission ] ) );
            $permissions = array_map( $mapFn, $explode );
            $posts[ $user_permission ] = json_encode( $permissions, 100 );
        }

        return $posts;
    }

    /** @throws ErrorParameterException */
    public function seedExport ( string $tableName, array $data ) : string
    {
        if ( empty( $tableName ) || empty( $data ) )
        {
            throw new ErrorParameterException;
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
     * @throws ErrorSqlException
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
                throw new ErrorSqlException( $sqlParser );
            }
        }

        setSuccessMessage();

        return $sqlParser;
    }

    /**
     * @param array $data Assoc-only.
     * @return array|string
     */
    public function keyValueExports ( array $data, bool $toString = true, ?callable $callable = null )
    {
        $dataAssocKeyMap = new DataAssocKeyMap;
        return $dataAssocKeyMap( $data, $toString, $callable );
    }

    public function selectExport ( string $tbl, array $data, bool $toString = true, ?callable $callable = null ) : string
    {
        $dataKeyMap = new DataKeyMap;
        return $dataKeyMap( $tbl, $data, $toString, $callable );
    }

    /**
     * @throws ErrorArrayException
     * @param array $data
     */
    public function selectExports ( array $data ) : string
    {
        $data = $this->_selectExportFormat( $data );

        if ( empty( $tables = $data[ 'tables' ] ) )
        {
            throw new ErrorArrayException;
        }

        $fields = [];

        foreach ( $tables as $tbl )
        {
            if ( empty( $field = $data[ 'data' ][ $tbl ]  ) )
            {
                throw new ErrorArrayException;
            }
            /** @var string[] $fields */
            $fields[] = $this->selectExport( $tbl, $field );
        }
        
        return implode( ',', $fields );
    }

    private function _selectExportFormat ( array $data ) : array
    {
        $dataTables = []; $dataColumns = [];

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