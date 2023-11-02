<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorSqlException;
use Red2Horse\Exception\ErrorJsonException;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Password\getHashPass;
use function Red2Horse\Mixins\Functions\Password\getRandomString;
use function Red2Horse\Mixins\Functions\Sql\getUserField;
use function Red2Horse\Mixins\Functions\Sql\getUserGroupField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SessionHandle
{
	use \Red2Horse\Mixins\Traits\Object\TraitSingleton;

	private function __construct () {}

	public function regenerateSession ( array $userData ) : bool
	{
		if ( ! getBaseInstance( 'Authentication' )->isLogged() )
		{
			return false;
		}

		$userColumnID 	= getUserField( 'id' );
		$userColumnSess = getUserField( 'session_id' );
		$editSessionId 	= model( 'User/UserModel' ) 
			->toggleAllowedFields( [ 'id' ] )
			->edit(
				[ $userColumnID 	=> $userData[ $userColumnID ] ],
				[ $userColumnSess 	=> session_id() ], 
				1, 
				fn( $filter ) 		=> $filter->setNoExplode( 'kv', $userColumnSess )
			);

		if ( ! $editSessionId )
		{
			$errorLog = sprintf( 'Field: ( session_id: "%s" ) failed update', $userColumnID );
			getComponents( 'common' )->log_message( 'error', $errorLog );

			return false;
		}

		getBaseInstance( CookieHandle::class )->regenerateCookie();

		return true;
	}

	/** @param array @userData Associative */
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
		$cacheConfig 	= getConfig( 'cache' );
		$cacheName 		= $cacheConfig->userGroupId;
		$cachePath 		= $cacheConfig->getCacheName( $cacheName );

		$cacheComponent = getComponents( 'cache' );
		$cacheComponent->cacheAdapterConfig->storePath .= $cachePath;
		/** End cache config */

		$roleField 		= getUserGroupField( 'role' );
		$roleString 	= $roleJson[ $roleField ];
		helpers( [ 'password' ] );
		$randomString 	= getRandomString( $roleString );
		$roleData 		= [ $roleField => $roleString, 'hash' => $randomString ];
		$userData[ $roleField ] = $roleData;

		helpers( [ 'password' ] );
		$roleDB 		= [ $roleField => $roleString, 'hash' => getHashPass( $randomString ) ];
		$updateSet 		= [ $roleField => json_encode( $roleDB ) ];

		$userId 		= $userData[ getUserField( 'id' ) ];

		if ( $cacheConfig->enabled && $cachedData = $cacheComponent->get( $cacheName ) )
		{
			$cachedData[ $userId ] = $roleDB;
			$cacheComponent->set( $cacheName, $cachedData, $cacheConfig->cacheTTL );
		}
		else
		{
			$updateWhere 	= [ 'user_group.id' => $userData[ 'user.group_id' ] ];
			$updateFilter 	= fn( $filter ) => $filter->setNoExplode( 'kv', $roleField );

			/** Update to DB */
			if ( ! model( 'user_group' )->edit( $updateSet , $updateWhere, 1, $updateFilter ) )
			{
				$errorLog = sprintf( 'user-id: "%s" logged-in, but update failed', $userId );
				getComponents( 'common' )->log_message( 'error', $errorLog );

				throw new ErrorSqlException( 'Cannot update: "Authentication"' );
			}
		}

		/** Set session */
		getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
	}
}