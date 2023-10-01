<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\
{
	Classes\SqlClass,
	Traits\TraitSingleton
};

use function Red2Horse\Mixins\Functions\
{
	getComponents,
	getInstance
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Validation
{
	use TraitSingleton;

	/** User columns */
	protected string $user_id = 'id';
	protected string $user_groupId = 'group_id';
	protected string $user_username = 'username';
	protected string $user_password = 'password';
	protected string $user_email = 'email';
	protected string $user_status = 'status';
	protected string $user_lastActivity = 'last_activity';
	protected string $user_lastLogin = 'last_login';
	protected string $user_createdAt = 'created_at';
	protected string $user_updatedAt = 'updated_at';
	protected string $user_deletedAt = 'deleted_at';
	protected string $user_sessionId = 'session_id';
	protected string $user_selector = 'selector';
	protected string $user_token = 'token';
	protected string $user_captcha = 'captcha';

	/** User group columns */
	protected string $userGroup_id = 'id';
	protected string $userGroup_name = 'name';
	protected string $userGroup_permission = 'permission';
	protected string $userGroup_role = 'role';
	protected string $userGroup_deletedAt = 'deleted_at';

	private function __construct () {}

	public function reInit() : void
	{
		getInstance( SqlClass::class )->reInit();
		getComponents( 'validation' )->reInit();
	}

	public function __set( $key, $value )
	{
		$this->$key = strtolower( $value );
	}

	public function __get ($name )
	{
		return $this->$name;
	}
}