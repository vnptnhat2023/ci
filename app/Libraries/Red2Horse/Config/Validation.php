<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Classes\Sql\SqlClass;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Instance\
{
	getComponents,
	getInstance
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Validation
{
	use TraitSingleton;

	// User columns
	protected 		string 		$user_id 				= 'id';
	protected 		string 		$user_groupId 			= 'group_id';
	protected 		string 		$user_username 			= 'username';
	protected 		string 		$user_password 			= 'password';
	protected 		string 		$user_email 			= 'email';
	protected 		string 		$user_status 			= 'status';
	protected 		string 		$user_lastActivity 		= 'last_activity';
	protected 		string 		$user_lastLogin 		= 'last_login';
	protected 		string 		$user_createdAt 		= 'created_at';
	protected 		string 		$user_updatedAt 		= 'updated_at';
	protected 		string 		$user_deletedAt 		= 'deleted_at';
	protected 		string 		$user_sessionId 		= 'session_id';
	protected 		string 		$user_selector 			= 'selector';
	protected 		string 		$user_token 			= 'token';
	protected 		string 		$user_captcha 			= 'captcha';
	protected		string		$user_statusList		= 'status_list';

	// User group columns
	protected 		string 		$userGroup_id 			= 'id';
	protected 		string 		$userGroup_name 		= 'name';
	protected 		string 		$userGroup_permission 	= 'permission';
	protected 		string 		$userGroup_role 		= 'role';
	protected 		string 		$userGroup_deletedAt 	= 'deleted_at';

	// Throttle columns
	protected 		string 		$throttle_id 			= 'id';
	protected 		string 		$throttle_attempt		= 'attempt';
	protected 		string 		$throttle_ip 			= 'ip';
	protected 		string 		$throttle_createdAt 	= 'created_at';
	protected 		string 		$throttle_updatedAt 	= 'updated_at';

	// Database columns
	protected 		string 		$database_hostname 		= 'db_host';
	protected 		string 		$database_username 		= 'db_username';
	protected 		string 		$database_password 		= 'db_password';
	protected 		string 		$database_database 		= 'db_database';
	protected 		string 		$database_port 			= 'database_port';

    private 		string 		$prefix;
    private 		string 		$suffix;

	private function __construct () {}

	public function reInit() : void
	{
		getInstance( SqlClass::class )	->reInit();
		getComponents( 'validation' )	->reInit();
	}

	public function __set( $key, $value )
	{
		$key 		= $this->_getStarFix( $this->$key );
		$this->$key = strtolower( $value );
	}

	public function __get ($key )
	{
		return $this->_getStarFix( $this->$key );
	}

	private function _getStarFix ( string $str ) : string
	{
		return sprintf(
			'%s%s%s', 
			$this->prefix ?? '', 
			$str, 
			$this->suffix ?? '' 
		);
	}
}