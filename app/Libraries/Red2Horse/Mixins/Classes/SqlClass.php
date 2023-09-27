<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes;

use Red2Horse\Mixins\{
    Interfaces\SqlClassInterface,
    Traits\TraitSingleton
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlClass implements SqlClassInterface
{
	use TraitSingleton;

	private array $defaultTables = [ 'user', 'user_group' ];

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

	private function __construct () {}
	
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
	public function setTable( string $table, string $name ) : bool
	{
		if ( in_array( $table, $this->defaultTables ) )
		{
			throw new \Error( 'Table name cannot contain [ user or user_group ].' );
		}

		$this->database[ 'tables' ][ $table ] = $name;

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

	public function getFields( array $keys, string $table )
	{
		$column = $this->database[ $this->getTable( $table ) ];
		$fn = fn( $value ) => is_array( $value ) && isset( $value[ 0 ] )
			? $value[ 0 ] 
			: $value;

		$column = array_map( $fn, $column );

		return array_intersect( $column, $keys );
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