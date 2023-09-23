<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Sql
{
	use TraitSingleton;

	/** Select & import */
	public array $tables = [

		'tables' => [
			'user' => 'user',
			'user_group' => 'user_group'
		],

		'user' => [
			// keys => alias keys
			'id' => 'id',
			'username' => 'username',
			'email' => 'email',
			'status' => 'status',
			'last_activity' => 'last_activity',
			'last_login' => 'last_login',
			'created_at' => 'created_at',
			'updated_at' => 'updated_at',
			'session_id' => 'session_id',
			'selector' => 'selector',
			'token' => 'token',

			// Sql
			'group_id' => 'group_id',
			'password' => 'password',
			'deleted_at' => 'deleted_at'
		],

		'user_group' => [
			// keys => alias keys
			'id' => [ 'id', 'as', 'group_id' ],
			'name' => [ 'name', 'as', 'group_name' ],
			'permission' => 'permission',
			'role' => 'role',
			'deleted_at' => 'deleted_at'// Import only
		]
	];
}