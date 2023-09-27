<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes;

use Red2Horse\Facade\Auth\Message;
use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\
{
    getTable,
    getColumn,
    getComponents,
    getInstance,
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlClassExport
{
    use TraitSingleton;

    private function __construct () { }

    public function requestToSql ( string $tableName, array $intersect = [] ) : array
    {
        $validation = getComponents( 'validation' );
        $req = getComponents( 'request' );
        $keys = array_keys( getColumn( $tableName ) );
        $return = [ 'intersect' => $intersect ];

        if ( empty( $posts = $req->post() ) ) { return $return; }

        if ( ! empty( $intersect = array_intersect( $keys, $intersect ) ) )
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

        $sql = $this->export( $tableName, $posts );

        $message = getInstance( Message::class );
        $message::$successfully = true;
        $message::$success[] = getComponents( 'common' )->lang( 'Red2Horse.successSeeder' );

        return [ 'intersect' => $intersect, 'sql' => $sql ];
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

    public function importUserGroup () : string
    {
        $tableUserGroup = getTable( 'user_group' );
        $userGroup = getColumn( 'user_group' );
        extract( $userGroup );

        $userGroupSql = "DROP TABLE IF EXISTS `{$tableUserGroup}`;
        CREATE TABLE IF NOT EXISTS `{$tableUserGroup}` (
        `{$id[ 0 ]}` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `{$name[ 0 ]}` varchar(64) NOT NULL DEFAULT 'guest',
        `{$role}` varchar(64) NOT NULL DEFAULT 'unknown',
        `{$permission}` varchar(512) DEFAULT NULL,
        `{$deleted_at}` date DEFAULT NULL,
        PRIMARY KEY (`{$id[ 0 ]}`)
        ) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;";

        return $userGroupSql;
    }

    public function importUser () : string
    {
        $tableUser = getTable( 'user' );
        $userField = getColumn( 'user' );
        extract( $userField );

        $userSql = "DROP TABLE IF EXISTS `{$tableUser}`;
            CREATE TABLE IF NOT EXISTS `{$tableUser}` (
            `{$id}` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `{$group_id}` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `{$username}` varchar(32) NOT NULL DEFAULT 'unknown',
            `{$email}` varchar(128) NOT NULL DEFAULT 'unknown',
            `{$password}` varchar(64) NOT NULL DEFAULT 'unknown',
            `{$status}` enum('active','inactive','banned') NOT NULL DEFAULT 'inactive',
            `{$selector}` varchar(255) DEFAULT NULL,
            `{$token}` varchar(255) DEFAULT NULL,
            `{$last_login}` varchar(64) DEFAULT NULL,
            `{$last_activity}` datetime DEFAULT NULL,
            `{$session_id}` varchar(40) DEFAULT NULL,
            `{$created_at}` date DEFAULT NULL,
            `{$updated_at}` date DEFAULT NULL,
            `{$deleted_at}` date DEFAULT NULL,
        PRIMARY KEY (`{$id}`),
        UNIQUE KEY `{$email}` (`{$email}`),
        UNIQUE KEY `{$username}` (`{$username}`),
        KEY `{$group_id}` (`{$group_id}`)
        ) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;";

        return $userSql;
    }

    public function sqlGetColumn ( array $userColumns, bool $join = true ) : string
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

    public function sqlGetColumns ( array $addColumns = [], bool $join = true ) : string
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