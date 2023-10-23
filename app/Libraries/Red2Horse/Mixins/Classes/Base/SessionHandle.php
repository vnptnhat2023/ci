<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorSqlException;
use Red2Horse\Exception\ErrorArrayException;
use Red2Horse\Exception\ErrorJsonException;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\BaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Model\model;
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

		$userColumnID = getField( 'id', 'user' );
		$editSet = [ $userColumnID => $userData[ $userColumnID ] ];
		$editWhere = [ getField( 'session_id', 'user' ) => session_id() ];
		$editFilter = function( $filter ) {
			$filter->setNoExplode( 'kv', getField( 'session_id', 'user' ) );
		};

		if ( ! model( 'User/UserModel' ) ->edit( $editSet, $editWhere, 1, $editFilter ) )
		{
			$errorLog = sprintf( 'session_id: "%s" update failed', $userColumnID );
			getComponents( 'common' ) ->log_message( 'error', $errorLog );

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
			throw new ErrorJsonException( 'Invalid json format' );
		}

		$roleJson = json_decode( $userData[ getUserGroupField( 'role' ) ], true );

		if ( ! array_key_exists( getUserGroupField( 'role' ), $roleJson ) || ! array_key_exists( 'hash', $roleJson ) )
		{
			throw new ErrorJsonException( 'Invalid json format' );
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
				$roleColumn = getField( 'role', 'user_group' );
				$updateWhere = [
					getField( 'id', 'user_group', false, true ) => $userData[ 'group_id' ]
				];
				$setFilter = function( $filter ) use ( $roleColumn ) {
					$filter->setNoExplode( 'kv', $roleColumn );
				};
				/** Update to DB */
				if ( ! model( 'user_group' )->edit( $updateGroupData , $updateWhere, 1, $setFilter ) )
				{
					$errorLog = sprintf( 'user-id: "%s" logged-in, but update failed', $userId );
					getComponents( 'common' )->log_message( 'error', $errorLog );

					throw new ErrorSqlException( 'Cannot updated "authentication"' );
				}
			}

			/** Set session */
			getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
		// }
	}
}