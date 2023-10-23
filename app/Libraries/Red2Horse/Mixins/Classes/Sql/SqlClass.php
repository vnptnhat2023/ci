<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Sql;

use Red2Horse\Exception\ErrorArrayKeyNotFoundException;
use Red2Horse\Exception\ErrorParameterException;
use Red2Horse\Mixins\Interfaces\Sql\SqlClassInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Event\trigger;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Sql\getField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SqlClass implements SqlClassInterface
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

	private function __construct ( ?callable $configPreFields = null )
	{
		trigger( 'before_sql_class_init' );
		$this->reInit( $configPreFields );
		trigger( 'after_sql_class_init' );
	}

	public function reInit ( ?callable $configPreFields = null ) : void
	{
		$configValidation = getConfig( 'validation' );
		$common = getComponents( 'common' );

		$database = [];
		$tables = $this->database[ 'tables' ];
		$userGroupTable = $tables[ 'user_group' ];

		foreach ( $tables as $table )
		{
			$fields = array_keys( $this->database[ $table ] );

			foreach ( $fields as $field )
			{
				/** @todo configPreFields -> props: "throttle_" */
				$configField = ( $table == $userGroupTable )
					? 'userGroup_' . $common->camelCase( $field )
					: 'user_' . $common->camelCase( $field );

				if ( is_array( $this->database[ $table ][ $field ] ) )
				{
					$fieldArray = $this->database[ $table ][ $field ];
					$fieldArray[ 0 ] = $configValidation->$configField;
					$database[ $table ][ $field ] = $fieldArray;
				}
				else
				{
					$database[ $table ][ $field ] = $configValidation->$configField;
				}
			}
		}

		$this->database = $database;
		$this->database[ 'tables' ] = $tables;
	}

	public function getData ( ?string $key = null )
	{
		return ( null === $key ) ? $this->database : $this->database[ $key ];
	}

	/**
	 * @throws ErrorArrayKeyNotFoundException
	 * @return false|string
	 */
	public function getTable( string $key, bool $getKey = false, bool $throw = true, bool $defaultReturn = false )
	{
		#dd( $this->database);
		$table = $this->database[ 'tables' ];#dd($table);

		if ( ! array_key_exists( $key, $table ) || empty( $table[ $key ] ) )
		{
			if ( $throw )
			{
				throw new ErrorArrayKeyNotFoundException( sprintf( 'Undefined table: %s', $key ) );
			}
			else if ( $defaultReturn )
			{
				return $key;
			}
			else
			{
				return false;
			}
		}

		if ( $getKey )
		{
			return $key;
		}

		return $table[ $key ];
	}

	private function _filter ( string $str ) : string
	{
		$isEscape = getConfig( 'sql' )->esc;

		if ( $isEscape )
		{
			$str = getComponents( 'common' )->esc( $str );
		}

		return strtolower( $str );
	}

	/** @param mixed $value */
	public function setTable( string $table, string $name ) : bool
	{
		if ( in_array( $table, $this->database[ 'tables' ] ) )
		{
			throw new ErrorParameterException( 'Parameter 1: "table" not in: "user, user_group"' );
		}

		$this->database[ 'tables' ][ $table ] = $this->_filter( $name );

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
			throw new ErrorParameterException( 'Parameter 1: "key" cannot equal table.' );
		}

		$this->database[ $key ] = $this->_filter( $value );

		return true;
	}

	public function getFields( array $keys, string $table, bool $columnsFormat = true, bool $keysFormat = true ) : array
	{
		$column = $this->database[ $this->getTable( $table ) ];

		/** Column */
		if ( $columnsFormat )
		{
			$fn = fn( $value ) => is_array( $value ) && isset( $value[ 0 ] )
				? $value[ 0 ]
				: $value;

			$column = array_map( $fn, $column );
		}

		/** Keys */
		if ( $keysFormat )
		{
			$keys = array_map( fn( string $key ) => getField( $key, $table ), $keys );
		}

		return array_intersect( $column, $keys );
	}

	public function getField(
		string $key, 
		string $table, 
		bool $getKey = false, 
		bool $getTableKey = false, 
		bool $throw = true )
	{
		if ( ! $table = $this->getTable( $table, false, false ) )
		{
			return false;
		}

		if ( '*' === $key )
		{
			return sprintf( '%s.%s', $table, $key );
		}

		if ( array_key_exists( $key, $this->database[ $table ] ) )
		{
			if ( $getKey )
			{
				return $getTableKey ? sprintf( '%s.%s', $table, $key ) : $key;
			}

			$field = $this->database[ $table ][ $key ];

			if ( $getTableKey )
			{
				if ( is_array( $field ) )
				{
					$fieldTableKey = array_key_exists( 0, $field ) ? $field[ 0 ] : $field;
				}
				else
				{
					$fieldTableKey = $field;
				}
				
				return sprintf( '%s.%s', $table, $fieldTableKey );
			}

			return $field;
		}

		if ( $throw )
		{
			throw new ErrorArrayKeyNotFoundException( sprintf( 'Field: "%s" not found', $key ) );
		}

		return false;
	}

	/** @param mixed $value */
	public function setField( string $key, string $table, $value ) : bool
	{
		if ( ! $table = $this->getTable( $key ) )
		{
			throw new ErrorArrayKeyNotFoundException( sprintf( 'Undefined table: "%s"', $table ) );
		}

		$this->database[ $table ][ $key ] = $this->_filter( $value );

		return true;
	}
}