<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Validation
{
	use TraitSingleton;

	/** User table */
	public static string $id = 'id';
	public static string $username = 'username';
	public static string $password = 'password';
	public static string $email = 'email';
	public static string $status = 'status';
	public static string $lastActivity = 'last_activity';
	public static string $lastLogin = 'last_login';
	public static string $createdAt = 'created_at';
	public static string $updatedAt = 'updated_at';
	public static string $sessionId = 'session_id';
	public static string $selector = 'selector';
	public static string $token = 'token';
	public static $captcha = 'captcha';

	/** User group table */
	public static string $groupId = 'id';
	public static string $groupName = 'name';
	public static string $groupPermission = 'permission';
	public static string $groupRole = 'role';
	public static string $groupDeletedAt = 'deleted_at';

	private function __construct () {}
}