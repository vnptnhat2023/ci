<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class Sql
{
	use TraitSingleton;
    /*
	|--------------------------------------------------------------------------
	| SQL syntax select user columns names
	|--------------------------------------------------------------------------
	*/
	public function getColumString ( array $columns = [], bool $join = true ) : string
	{
		$columns = [
			# user
			'user.id',
			'user.username',
			'user.email',
			'user.status',
			'user.last_activity',
			'user.last_login',
			'user.created_at',
			'user.updated_at',
			'user.session_id',
			'user.selector',
			'user.token',
			...$columns
		];

		if ( $join ) {
			# user_group
			$columns[] = 'user_group.id as group_id';
			$columns[] = 'user_group.name as group_name';
			$columns[] = 'user_group.permission';
			$columns[] = 'user_group.role';
		}

		return implode( ',', $columns );
	}
}