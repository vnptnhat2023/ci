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
    getField,
    getFields,
    getHashPass,
    getInstance,
    setSuccessMessage,
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlClassExport
{
    use TraitSingleton;

    // DROP TABLE IF EXISTS `:user:`;
    public string $userTemplateTbl = '
    CREATE TABLE IF NOT EXISTS `:user:` (
    `:id:` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `:group_id:` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `:username:` varchar(32) NOT NULL DEFAULT "unknown",
    `:email:` varchar(128) NOT NULL DEFAULT "unknown",
    `:password:` varchar(64) NOT NULL DEFAULT "unknown",
    `:status:` enum("active","inactive","banned") NOT NULL DEFAULT "inactive",
    `:selector:` varchar(255) DEFAULT NULL,
    `:token:` varchar(255) DEFAULT NULL,
    `:last_login:` varchar(64) DEFAULT NULL,
    `:last_activity:` datetime DEFAULT NULL,
    `:session_id:` varchar(40) DEFAULT NULL,
    `:created_at:` date DEFAULT NULL,
    `:updated_at:` date DEFAULT NULL,
    `:deleted_at:` date DEFAULT NULL,
    PRIMARY KEY (`:id:`),
    UNIQUE KEY `:email:` (`:email:`),
    UNIQUE KEY `:username:` (`:username:`),
    KEY `:group_id:` (`:group_id:`)
    ) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;';

    // DROP TABLE IF EXISTS `:user_group:`;
    public string $userGroupTemplateTbl = '
    CREATE TABLE IF NOT EXISTS `:user_group:` (
    `:id:` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `:name:` varchar(64) NOT NULL DEFAULT "guest",
    `:role:` varchar(64) NOT NULL DEFAULT "unknown",
    `:permission:` varchar(512) DEFAULT NULL,
    `:deleted_at:` date DEFAULT NULL,
    PRIMARY KEY (`:id:`)
    ) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;';

    private function __construct () { }

    /**
     * Seeder
     * @return array ( intersect ) | ( intersect, sql )
     */
    public function seed ( string $tableName, array $intersect = [], bool $query = false ) : array
    {
        $validation = getComponents( 'validation' );
        $req = getComponents( 'request' );
        $keys = getFields( $intersect, $tableName );
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

        $user_password = getField( 'password', getTable( 'user' ) );
        if ( array_key_exists( $user_password, $posts ) )
        {
            $posts[ $user_password ] = getHashPass( $posts[ $user_password ] );
        }

        if ( ! $validation->isValid( $posts, $rules ) )
        {
            getInstance( Message::class )::$errors += $validation->getErrors();
            return $return;
        }

        $sql = $this->export( $tableName, $posts );

        if ( $query && ! getComponents( 'user' )->querySimple( $sql ) )
        {
            throw new \Error( sprintf( 'Cannot query %s:%s', __METHOD__, __LINE__ ) );
        }

        $message = getInstance( Message::class );
        $message::$successfully = true;
        $message::$success[] = getComponents( 'common' )->lang( 'Red2Horse.successSeeder' );

        $return[ 'sql' ] = $sql;
        return $return;
    }

    public function export ( string $tableName, array $data ) : string
    {
        if ( empty( $tableName ) || empty( $data ) )
        {
            $errorStr = sprintf( 'Invalid parameters format. %s:%s:%s', __FILE__, __METHOD__, __LINE__ );
            throw new \Error( sprintf( $errorStr ), 406 );
        }

        $columns = implode( ',', array_map( fn( $str ) => "`{$str}`" , array_keys( $data ) ) );
        $values = implode( ',', array_map( fn( $str ) => "'{$str}'" , array_values( $data ) ) );

        $sql = sprintf(
            'INSERT INTO `%s`(%s) VALUES(%s);',
            getTable( $tableName ),
            $columns,
            $values
        );

        return $sql;
    }
    /** End seeder */


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

        $tableVarName = sprintf( '%sTemplateTbl', $this->_camelCase( $tableKeyName ) );

        $match = function( $match ) use ( $vars ) { return $vars[ $match[ 1 ] ]; };
        $sqlParser = preg_replace_callback( '/:(.*?):/', $match, $this->{ $tableVarName } );

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

    private function _camelCase ( string $str, bool $ucfirst = false ) : string
	{
		$str = str_replace( ' ', '', ucwords( str_replace( [ '-', '_' ], ' ', $str ) ) );

		if ( ! $ucfirst )
		{
			$str[ 0 ] = strtolower( $str[ 0 ] );
		}

		return $str;
	}

    public function sqlSelectColumn ( array $userColumns, bool $join = true ) : string
    {
        if ( empty( $userColumns ) )
        {
            throw new \Error( 'Empty column variable.' );
        }

        $tableUser = getTable( 'user' );
        $userFields = getColumn( 'user' );

        $test = ( getComponents( 'common' )->isAssocArray( $userColumns ) )
            ? array_keys( $userColumns )
            : $userColumns;

        $diffs = array_diff( $test, array_keys( $userFields ) );
        if ( ! empty( $diffs ) )
        {
            throw new \Error( 'Not Acceptable' , 406 );
        } 

        $uCols = '';
        foreach ( $userColumns as $key => $value )
        {
            if ( is_string( $key ) )
            {
                $uCols .= sprintf( '%s.%s as %s,', $tableUser, $key, $value );
            }
            else
            {
                $uCols .= sprintf( '%s.%s,', $tableUser, $value );
            }
        }

        if ( $join )
        { # user_group
            $tableUserGroup = getTable( 'user_group' );
            $userGroup = getColumn( 'user_group' );
            extract( $userGroup );

            $columns[] = "{$tableUserGroup}.{$id[ 0 ]} {$id[ 1 ]} {$id[ 2 ]}";
            $columns[] = "{$tableUserGroup}.{$name[ 0 ]} {$name[ 1 ]} {$name[ 2 ]}";
            $columns[] = "{$tableUserGroup}.{$permission}";
            $columns[] = "{$tableUserGroup}.{$role}";
        }

        $userGroup = implode( ',', $columns );
        $str = $uCols . $userGroup;

        return $str;
    }

    public function sqlSelectColumns ( array $addColumns = [], bool $join = true ) : string
    {
        $tableUser = getTable( 'user' );
        $userFields = getColumn( 'user' );
        extract( $userFields );
        
        $columns = [ # user
            "{$tableUser}.{$id}",
            "{$tableUser}.{$username}",
            "{$tableUser}.{$email}",
            "{$tableUser}.{$status}",
            "{$tableUser}.{$last_activity}",
            "{$tableUser}.{$last_login}",
            "{$tableUser}.{$created_at}",
            "{$tableUser}.{$updated_at}",
            "{$tableUser}.{$session_id}",
            "{$tableUser}.{$selector}",
            "{$tableUser}.{$token}",
            ...$addColumns
        ];

        if ( $join )
        { # user_group
            $tableUserGroup = getTable( 'user_group' );
            $userGroup = getColumn( 'user_group' );
            extract( $userGroup );

            $columns[] = "{$tableUserGroup}.{$id[ 0 ]} {$id[ 1 ]} {$id[ 2 ]}";
            $columns[] = "{$tableUserGroup}.{$name[ 0 ]} {$name[ 1 ]} {$name[ 2 ]}";
            $columns[] = "{$tableUserGroup}.{$permission}";
            $columns[] = "{$tableUserGroup}.{$role}";
        }

        return implode( ',', $columns );
    }
}