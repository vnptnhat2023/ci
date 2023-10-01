<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\
{
    getComponents,
    getConfig,
    getField,
    getInstance,
    getTable,
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
	 */
	private function _run ( array $args, string $condition = 'or' ) : bool
	{
		if ( ! $this->_unauthorized( $args ) )
		{
			return false;
		}

		/** @var array<int, int> @usernameSess */
		$usernameSess = $this->_getUserData( [ getField( 'id', 'user' ) ] );

		if ( empty( $usernameSess[ 0 ] ) )
		{
			getComponents( 'common' )->log_message( 'error', __FILE__ . __LINE__ . 'Not logged in, ...' );
			return false;
		}

		$userDataArgs = [
			sprintf( '%s.%s', getTable( 'user' ), getField( 'id', 'user' ) ) => $usernameSess[ 0 ],
		];

		$cacheConfig = getConfig( 'cache' );
		$cachePath = $cacheConfig->getCacheName( $cacheConfig->userGroupId );
		$cacheComponent = getComponents( 'cache' );
		$cacheComponent->cacheAdapterConfig->storePath .= $cachePath;

		if ( ! $cacheConfig->enabled )
		{
			$cacheComponent->delete( $cacheConfig->userGroupId );
		}

		$userData = $cacheComponent->get( $cacheConfig->userGroupId );

		if ( ! $userData )
		{
			/** @var array $userDB */
			$userDB = getComponents( 'user' )->getUserWithGroup(
				\Red2Horse\Mixins\Functions\sqlSelectColumn( [ getField( 'id', 'user' )  => 'user_id' ] ),
				$userDataArgs
			);

			unset( $userData );
			$userData[ $usernameSess[ 0 ] ] = json_decode( $userDB[ getField( 'role', 'user_group' ) ], true );
			/** Set cache from DB */
			$cacheComponent->set( $cacheConfig->userGroupId, $userData, $cacheConfig->cacheTTL );
		}
		
		$userData = $userData[ $usernameSess[ 0 ] ];

		if ( empty( $userData ) )
		{
			getComponents( 'common' )->log_message( 
				'error', __FILE__ . __LINE__ . 'Invalid data format.'
			);

			return false;
		}

		if ( ! getVerifyPass( $this->sessionData[ 'hash' ], $userData[ 'hash' ] ) )
		{
			getComponents( 'common' )->log_message( 
				'error', __FILE__ . __LINE__ . 'Unauthorized.'
			);

			return false;
		}

		unset( $this->sessionData[ 'hash' ] );

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