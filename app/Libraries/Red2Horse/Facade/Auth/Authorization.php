<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\
{
    getComponents,
    getConfig,
    getInstance,
    getVerifyPass
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

final class Authorization
{
	use TraitSingleton;
	private array $sessionData;

	private function __construct () {}

	/**
	 * @throws \Error Unauthorized, 401
	 * @param string $condition [ or, !or, not_or, and, !and, not_and ]
	 */
	public function withSession ( string $sessKey, array $data, string $condition = 'or' ) : bool
	{
		$this->sessionData = $this->_getUserData( ( array ) $sessKey );

		if ( ! $this->sessionData )
		{
			throw new \Error( 'Unauthorized.', 401 );
		}

		return $this->_run( $data, $condition );
	}

	/**
	 * @param array $args
	 * @param string $k [ or, and, except; Default or ]
	 * @throws \Error
	 */
	private function _run ( array $args, string $condition = 'or' ) : bool
	{
		if ( ! $this->_unauthorized( $args ) )
		{
			return false;
		}

		$configTables = getConfig( 'Sql' )->tables;
		$userTable = $configTables[ $configTables[ 'tables' ][ 'user' ] ];
		$usernameId = $userTable[ 'id' ];
		/** @var array<int, int> @usernameSess */
		$usernameSess = $this->_getUserData( [ $usernameId ] );

		if ( empty( $usernameSess[ 0 ] ) )
		{
			throw new \Error( 'Unauthorized' , 401 );
		}

		$userDataArgs = [ "{$configTables[ 'tables' ][ 'user' ]}.{$usernameId}" => $usernameSess[ 0 ] ];
		$cachePath = getConfig( 'cache' )->getCacheName( 'get_user_with_group_user_id' );
		getComponents( 'cache' )->cacheAdapterConfig->storePath .= $cachePath;

		if ( ! $cacheData = getComponents( 'cache' )->get( 'get_user_with_group_user_id' ) )
		{
			/** @var array $userData */
			$userData = getComponents( 'user' )->getUserWithGroup(
				\Red2Horse\Mixins\Functions\sqlGetColumn( [ 'id'  => 'user_id' ] ),
				$userDataArgs
			);

			$cacheData[ $usernameSess[ 0 ] ] = $userData;
			getComponents( 'cache' )->set( 'get_user_with_group_user_id', $cacheData, 2592000 );
		}

		$userData = $cacheData[ $usernameSess[ 0 ] ];

		if ( ! getComponents( 'common' )->valid_json( $userData[ 'role' ] ) )
		{
			throw new \Error( 'Invalid json format .', 406 );
		}

		$roleData = json_decode( $userData[ 'role' ], true );
		if ( ! getVerifyPass( $this->sessionData[ 'hash' ], $roleData[ 'hash' ] ) )
		{
			throw new \Error( 'Unauthorized', 401 );
		}

		switch ( $condition )
		{
			case '!and': case 'not_and': return ! $this->_and( $args );
			case '!or': case 'not_or': return ! $this->_or( $args );
			case 'and': return $this->_and( $args );
			default: return $this->_or( $args );
		}
	}

	private function _or ( array $args ) : bool
	{
		if ( $args == $this->sessionData )
		{
			return true;
		}
		
		$diff = array_diff( $args, $this->sessionData );

		if ( count( $diff ) != count( $args ) )
		{
			return true;
		}

		return false;
	}

	private function _and ( array $args ) : bool
	{
		return $args == $this->sessionData;
	}

	private function _getUserData ( array $args ) : array
	{
		$userData = [];
		foreach ( $args as $key )
		{
			$userData = ( array ) getInstance( Authentication::class )->getUserdata( $key );
		}

		return $userData;
	}

	private function _unauthorized( array $args ) : bool
	{
		return ! empty( $this->sessionData ) || ! empty( $args );
	}
}