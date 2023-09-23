<?php

declare( strict_types=1 );
namespace Red2Horse\Mixins\Functions;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

function sqlFromUserGroups () : string
{
    $configSql = getConfig( 'sql' );
    $tableUserGroupName = $configSql->tables[ 'tables' ][ 'user_group' ];
    $userGroup = $configSql->tables[ $tableUserGroupName ];

    extract( $userGroup );

    $userGroupSql = "DROP TABLE IF EXISTS `{$tableUserGroupName}`;
    CREATE TABLE IF NOT EXISTS `{$tableUserGroupName}` (
    `{$id[ 0 ]}` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `{$name[ 0 ]}` varchar(64) NOT NULL DEFAULT 'guest',
    `{$role}` varchar(64) NOT NULL DEFAULT 'unknown',
    `{$permission}` varchar(512) DEFAULT NULL,
    `{$deleted_at}` date DEFAULT NULL,
    PRIMARY KEY (`{$id[ 0 ]}`)
    ) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;";

    return $userGroupSql;
}

function sqlFromUsers () : string
{
    $configSql = getConfig( 'sql' );
    $tableUserName = $configSql->tables[ 'tables' ][ 'user' ];
    $userField = $configSql->tables[ $tableUserName ];
    extract( $userField );

    $userSql = "DROP TABLE IF EXISTS `{$tableUserName}`;
        CREATE TABLE IF NOT EXISTS `{$tableUserName}` (
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

function sqlGetColumn ( array $userColumns, bool $join = true ) : string
{
    if ( empty( $userColumns ) )
    {
        throw new \Error( 'Empty column variable.' );
    }

    $configSql = getConfig( 'sql' );
    $tableUser = $configSql->tables[ 'tables' ][ 'user' ];
    $userFields = $configSql->tables[ $tableUser ];

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
        $tableUserGroupName = $configSql->tables[ 'tables' ][ 'user_group' ];
        $userGroup = $configSql->tables[ $tableUserGroupName ];

        extract( $userGroup );

        $columns[] = "{$tableUserGroupName}.{$id[ 0 ]} {$id[ 1 ]} {$id[ 2 ]}";
        $columns[] = "{$tableUserGroupName}.{$name[ 0 ]} {$name[ 1 ]} {$name[ 2 ]}";
        $columns[] = "{$tableUserGroupName}.{$permission}";
        $columns[] = "{$tableUserGroupName}.{$role}";
    }

    $userGroup = implode( ',', $columns );
    $str = $uCols . $userGroup;

    return $str;
}

function sqlGetColumns ( array $addColumns = [], bool $join = true ) : string
{
    $configSql = getConfig( 'sql' );
    $tableUser = $configSql->tables[ 'tables' ][ 'user' ];
    $userFields = $configSql->tables[ $tableUser ];
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
        $tableUserGroupName = $configSql->tables[ 'tables' ][ 'user_group' ];
        $userGroup = $configSql->tables[ $tableUserGroupName ];

        extract( $userGroup );

        $columns[] = "{$tableUserGroupName}.{$id[ 0 ]} {$id[ 1 ]} {$id[ 2 ]}";
        $columns[] = "{$tableUserGroupName}.{$name[ 0 ]} {$name[ 1 ]} {$name[ 2 ]}";
        $columns[] = "{$tableUserGroupName}.{$permission}";
        $columns[] = "{$tableUserGroupName}.{$role}";
    }

    return implode( ',', $columns );
}