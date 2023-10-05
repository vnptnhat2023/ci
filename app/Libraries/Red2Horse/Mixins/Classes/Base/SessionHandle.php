<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    getField,
    baseInstance,
	getHashPass,
    getRandomString,
    getTable,
    getUserGroupField
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SessionHandle
{
	use TraitSingleton;

	private function __construct () {}

	public function regenerateSession ( array $userData ) : bool
	{
		if ( ! baseInstance( Authentication::class )->isLogged() )
		{
			return false;
		}

		$userID = getField( 'id', 'user' );
		$isUpdated = getComponents( 'user' ) ->updateUser(
			$userID,
			[ getField( 'session_id', 'user' ) => session_id() ]
		);

		if ( ! $isUpdated )
		{
			$errStr = "The session_id: {$userID} update failed";
			getComponents( 'common' ) ->log_message( 'error', $errStr );

			return false;
		}

		baseInstance( CookieHandle::class )->regenerateCookie();

		return true;
	}

	public function roleHandle ( array $userData ) : void
	{
		/** DB or session */
		if ( ! getComponents( 'common' )->valid_json( $userData[ getUserGroupField( 'role' ) ] ) )
		{
			throw new \Error( 'Role: Invalid json format !', 406 );
		}

		$roleJson = json_decode( $userData[ getUserGroupField( 'role' ) ], true );

		if ( ! array_key_exists( getUserGroupField( 'role' ), $roleJson ) || ! array_key_exists( 'hash', $roleJson ) )
		{
			throw new \Error( 'Role: Invalid json format !', 406 );
		}
		/** end */

		/** Cache config */
		$cacheConfig = getConfig( 'cache' );
		$cacheName = $cacheConfig->userGroupId;
		$cachePath = $cacheConfig->getCacheName( $cacheName );

		$cacheComponent = getComponents( 'cache' );
		$cacheComponent->cacheAdapterConfig->storePath .= $cachePath;
		/** End cache config */

		$roleField = getUserGroupField( 'role' );
		// if ( $cacheConfig->enabled && $cacheData = $cacheComponent->get( $cacheName ) )
		// {
		// 	$sessId = ( int ) getField( 'id', 'user' );
		// 	$userData[ $roleField ] = $cacheData[ $sessId ];
		// 	getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
		// }
		// else
		// {
			$roleString = $roleJson[ $roleField ];
			$randomString = getRandomString( $roleString );
			$roleData = [ $roleField => $roleString, 'hash' => $randomString ];
			$userData[ $roleField ] = $roleData;

			$roleDB = [ $roleField => $roleString, 'hash' => getHashPass( $randomString ) ];
			$updateGroupData = [ $roleField => json_encode( $roleDB ) ];

			$userId = $userData[ getField( 'id', 'user' ) ];

			if ( $cacheConfig->enabled && $cachedData = $cacheComponent->get( $cacheName ) )
			{
				$cachedData[ $userId ] = $roleDB;
				$cacheComponent->set( $cacheName, $cachedData, $cacheConfig->cacheTTL );
			}
			else
			{
				$updated = baseInstance( Authentication::class )->loggedInUpdateData(
					$userId,
					$updateGroupData,
					getTable( 'user_group' )
				);

				/** Update to DB */
				if ( ! $updated )
				{
					getComponents( 'common' )->log_message(
						'error',
						"{ $userId } Logged-in, but update failed"
					);

					throw new \Error( 'Authentication cannot updated.', 406 );
				}
			}

			/** Set session */
			getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
		// }
	}
}