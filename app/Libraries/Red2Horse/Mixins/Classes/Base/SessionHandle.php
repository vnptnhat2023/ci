<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorSqlException;
use Red2Horse\Exception\ErrorArrayException;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\BaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Password\getHashPass;
use function Red2Horse\Mixins\Functions\Password\getRandomString;
use function Red2Horse\Mixins\Functions\Sql\getField;
use function Red2Horse\Mixins\Functions\Sql\getTable;
use function Red2Horse\Mixins\Functions\Sql\getUserGroupField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SessionHandle
{
	use \Red2Horse\Mixins\Traits\Object\TraitSingleton;

	private function __construct () {}

	public function regenerateSession ( array $userData ) : bool
	{
		if ( ! BaseInstance( 'Authentication' )->isLogged() )
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
			throw new ErrorArrayException( 'Invalid json format' );
		}

		$roleJson = json_decode( $userData[ getUserGroupField( 'role' ) ], true );

		if ( ! array_key_exists( getUserGroupField( 'role' ), $roleJson ) || ! array_key_exists( 'hash', $roleJson ) )
		{
			throw new ErrorArrayException( 'Invalid json format' );
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
				$updated = baseInstance( 'Authentication' )->loggedInUpdateData(
					$userId,
					$updateGroupData,
					getTable( 'user_group' )
				);

				/** Update to DB */
				if ( ! $updated )
				{
					getComponents( 'common' )
						->log_message( 'error', "{ $userId } Logged-in, but update failed" );

					throw new ErrorSqlException( 'Authentication cannot updated.' );
				}
			}

			/** Set session */
			getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
		// }
	}
}