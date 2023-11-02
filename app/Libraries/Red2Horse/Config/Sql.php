<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\
{
    Object\TraitSingleton,
    Object\TraitReadOnly
};

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Event\on;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Sql
{
	use TraitSingleton, TraitReadOnly;

    protected       bool        $esc            = true;
    protected       bool        $useQuery       = true;

	protected string $userTemplateTbl = '
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

    protected string $userGroupTemplateTbl = '
CREATE TABLE IF NOT EXISTS `:user_group:` (
`:id:` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
`:name:` varchar(64) NOT NULL DEFAULT "guest",
`:role:` varchar(64) NOT NULL DEFAULT "unknown",
`:permission:` varchar(512) DEFAULT NULL,
`:deleted_at:` date DEFAULT NULL,
PRIMARY KEY (`:id:`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;';

    protected string $throttleTemplateTbl = '
CREATE TABLE IF NOT EXISTS :throttle: (
:id: bigint UNSIGNED NOT NULL AUTO_INCREMENT,
:attempt: bigint UNSIGNED NOT NULL DEFAULT 0,
:ip: varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
:created_at: datetime DEFAULT NULL,
:updated_at: datetime DEFAULT NULL,
PRIMARY KEY (:id:)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;';
    protected       $insertTemplate           = 'INSERT INTO `%s` (%s) VALUES (%s)';
    protected       $replaceTemplate          = 'REPLACE INTO `%s` VALUES (%s)';
    protected       $updateTemplate           = 'UPDATE `%s` SET %s WHERE %s';
    protected       $updateJoinTemplate       = 'UPDATE %s JOIN %s SET %s WHERE %s';
    protected       $deleteTemplate           = 'DELETE FROM `%s` WHERE %s';
    protected       $deleteJoinTemplate       = 'DELETE JOIN %s FROM `%s` WHERE %s';
    
	private function __construct ()
    {
        helpers( [ 'event' ] );
        on ( 'after_sql_class_init', self::class );
        $this->after_sql_class_init();
    }

    public function after_sql_class_init ()
    {
        
    }
}