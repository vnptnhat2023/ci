<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Sql
{
	use TraitSingleton;

	/** Select & import */
	private array $database = [

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

	public function getData ( ?string $key = null )
	{
		return ( null === $key ) ? $this->database : $this->database[ $key ];
	}

	/**
	 * @throws \Error
	 */
	public function getTable( string $key )
	{
		if ( ! $this->database[ 'tables' ][ $key ] )
		{
			throw new \Error( 'Undefined table: ' . $key );
		}

		return $this->database[ 'tables' ][ $key ];
	}

	/** @param mixed $value */
	public function setTable( string $key, string $table, $value ) : bool
	{
		$this->database[ $table ][ $key ] = $value;

		return true;
	}

	/** @return mixed */
	public function getColumn( string $key )
	{
		$table = $this->getTable( $key );

		return $this->database[ $table ];
	}

	public function setColumn( string $key, string $value ) : bool
	{
		if ( $key == 'table' && ! $this->getTable( $key ) )
		{
			throw new \Error( 'The key cannot be table.' );
		}

		$this->database[ $key ] = $value;

		return true;
	}

	public function getField( string $key, string $table )
	{
		$table = $this->getTable( $table );

		return $this->database[ $table ][ $key ];
	}

	/** @param mixed $value */
	public function setField( string $key, string $table, $value ) : bool
	{
		if ( ! $table = $this->getTable( $key ) )
		{
			throw new \Error( 'Undefined table: ' . $table );
		}

		$this->database[ $table ][ $key ] = $value;

		return true;
	}
}