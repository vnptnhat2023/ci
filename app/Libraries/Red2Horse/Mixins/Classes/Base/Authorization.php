<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorArrayException;
use Red2Horse\Exception\ErrorUnauthorizedException;
use Red2Horse\Facade\Cache\CacheFacade;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Password\getVerifyPass;
use function Red2Horse\Mixins\Functions\Sql\getUserField;
use function Red2Horse\Mixins\Functions\Sql\getUserGroupField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Authorization
{
	use TraitSingleton;

	private array $sessionData;

	private function __construct () {}

	/**
	 * @throws ErrorUnauthorizedException Unauthorized, 401
	 * @param string $condition [ or, !or, not_or, and, !and, not_and ]
	 */
	public function withSession ( string $sessKey, array $data, string $condition = 'or' ) : bool
	{
		$this->sessionData = $this->_getUserData( ( array ) $sessKey );

		if ( ! $this->sessionData )
		{
			throw new ErrorUnauthorizedException( 'Unauthorized.', 401 );
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
		$usernameSess = $this->_getUserData( [ getUserField( 'id' ) ] );

		if ( empty( $usernameSess[ 0 ] ) )
		{
			return false;
		}

		$cacheConfig 			= getConfig( 'cache' );
		$cachePath 				= $cacheConfig->getCacheName( $cacheConfig->userGroupId );
		$cacheComponent 		= getComponents( 'cache' );

		$cacheComponent->cacheAdapterConfig->storePath .= $cachePath;

		if ( ! $cacheConfig->enabled )
		{
			$cacheComponent->delete( $cacheConfig->userGroupId );
		}

		$userData = $cacheComponent->get( $cacheConfig->userGroupId );

		if ( ! $userData )
		{
			$userDB = model( 'User/UserModel' )->first( [ 'user.id' => $usernameSess[ 0 ] ] );
			unset( $userData );
			$userData[ $usernameSess[ 0 ] ] = json_decode( $userDB[ getUserGroupField( 'role' ) ], true );
			
			/** @var CacheFacade $cacheComponent */
			$cacheComponent->set( $cacheConfig->userGroupId, $userData, $cacheConfig->cacheTTL );
		}
		
		$userData = $userData[ $usernameSess[ 0 ] ];

		if ( empty( $userData ) )
		{
			$logError = ( new ErrorArrayException() )->getMessage();
			getComponents( 'common' )->log_message( 'error', $logError );
			return false;
		}

		if ( ! getVerifyPass( $this->sessionData[ 'hash' ], $userData[ 'hash' ] ) )
		{
			$logError = ( new ErrorUnauthorizedException() )->getMessage();
			getComponents( 'common' )->log_message( 'error', $logError );
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
		$baseAuthentication = getBaseInstance( Authentication::class );
		$userData 			= [];

		foreach ( $args as $key )
		{
			$userData 		= ( array ) $baseAuthentication->getUserdata( $key );
		}

		return $userData;
	}

	private function _unauthorized( array $args ) : bool
	{
		return ! empty( $this->sessionData ) || ! empty( $args );
	}
}