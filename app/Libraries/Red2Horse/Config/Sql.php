<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class Sql
{
	use TraitSingleton;

	/** Select & import */
	public array $table = [

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

	/*public function getColumString ( array $columns = [], bool $join = true ) : string
	{
		$columns = [ # user
			'user.id', 'user.username', 'user.email', 'user.status', 'user.last_activity',
			'user.last_login', 'user.created_at', 'user.updated_at', 'user.session_id',
			'user.selector', 'user.token', ...$columns
		];

		if ( $join )
		{ # user_group
			$columns[] = 'user_group.id as group_id';
			$columns[] = 'user_group.name as group_name';
			$columns[] = 'user_group.permission';
			$columns[] = 'user_group.role';
		}

		return implode( ',', $columns );
	}*/
}