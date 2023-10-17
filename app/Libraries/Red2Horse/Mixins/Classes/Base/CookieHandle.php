<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\BaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Sql\getTable;
use function Red2Horse\Mixins\Functions\Sql\getUserField;
use function Red2Horse\Mixins\Functions\Sql\getUserGroupField;
use function Red2Horse\Mixins\Functions\Sql\selectExports;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CookieHandle
{
	use TraitSingleton;

	private string $hash = 'sha256';

	private array $userStatus = [ 'inactive', 'banned' ];

	private function __construct () {}

	public function regenerateCookie () : void
	{
		$cookieValue = password_hash( session_id(), PASSWORD_DEFAULT );
		$ttl = ( string ) getConfig( 'cookie' ) ->cookie;
		$cookieName = getConfig( 'cookie' ) ->cookie . '_test';

		getComponents( 'cookie' )->set_cookie( $cookieName , $cookieValue, $ttl );
	}

	public function cookieHandler () : bool
	{
		if ( ! $user = $this->_cookieValidate() )
		{
			return false;
		}

		$user = $this->_cookieHandleRefresh( $user );

		if ( getComponents( 'cache' )->isSupported() )
		{
			BaseInstance( 'SessionHandle' )->roleHandle( $user );
		}
		else
		{
			getComponents( 'session' )->set( getConfig( 'session' )->session, $user );
		}

		$this->regenerateCookie();

		return true;
	}

	/** CookieHandler private function */
	private function _cleanOldCookie ()
	{
		getComponents( 'cookie' )->delete_cookie( getConfig( 'cookie' )->cookie );
		return false;
	}

	/** @return array|false */
	private function _getCookieConfig ()
	{
		if ( ! getConfig( 'BaseConfig' )->useRememberMe )
		{
			return false;
		}

		$configCookie = getConfig( 'cookie' );
		$cookie = getComponents( 'cookie' );
		$userCookie = $cookie->get_cookie( $configCookie->cookie );

		if ( empty( $userCookie ) || ! is_string( $userCookie ) )
		{
			return false;
		}

		$separate = explode( ':', $userCookie, 2 );

		if ( empty( $separate[ 0 ] ) || empty( $separate[ 1 ] ) )
		{
			return $this->_cleanOldCookie();
		}

		return [ 'selector' => $separate[ 0 ], 'token' => $separate[ 1 ] ];
	}

	/**
	 * @return false|array
	 */
	private function _queryValidate ( $selector, $token )
	{
		$querySelect = selectExports( [ getTable( 'user' ) => [], getTable( 'user_group' ) => [] ] );
		$queryWhere = [ getUserField( 'selector' ) => $selector ];

		/** Query user, group */
		if ( [] === ( $user = getComponents( 'user' )->getUserWithGroup( $querySelect, $queryWhere ) ) )
		{
			return $this->_cleanOldCookie();
		}

		/** Validate cookie */
		$isValid = hash_equals( $user[ getUserField( 'token' ) ], hash( 'sha256', $token ) );
		$isUserIp = $user[ getUserField( 'last_login' ) ] == getComponents( 'request' )->getIPAddress();

		if ( ! $isValid || ! $isUserIp )
		{
			return $this->_cleanOldCookie();
		}

		return $user;
	}

	private function _userStatus ( array $user ) : bool
	{
		# Check status
		if ( in_array( $user[ getUserField( 'status' ) ] , $this->userStatus ) )
		{
			baseInstance( Message::class )->errorAccountStatus(
				$user[ getUserField( 'status' ) ], false, false
			);
			return $this->_cleanOldCookie();
		}

		return true;
	}

	private function _userCheckMultiLogin ( array $user ) : bool
	{
		# @Todo: declare inside the config file: is using this feature
		$isMultiLogin = baseInstance( 'Authentication' )
						->isMultiLogin( $user[ getUserField( 'session_id' ) ] );

		if ( ! $isMultiLogin )
		{
			baseInstance( 'Message' )->errorMultiLogin( true, [], false );
			return false;
		}

		return true;
	}

	/**
	 * @return array|false
	 */
	private function _cookieValidate ()
	{
		if ( ! $cookieConfig = $this->_getCookieConfig() )
		{
			return false;
		}

		if ( ! $user = $this->_queryValidate ( $cookieConfig[ 'selector' ], $cookieConfig[ 'token' ] ) )
		{
			return false;
		}

		if ( ! $this->_userStatus( $user ) )
		{
			return false;
		}

		if ( ! $this->_userCheckMultiLogin( $user ) )
		{
			return false;
		}

		return $user;
	}

	private function _cookieHandleRefresh ( array $user ) : array
	{
		$keyUserId = ( int ) $user[ getUserField( 'id' ) ];

		# Refresh cookie
		$setCookieParam = getComponents( 'common' )
			->lang( 'errorCookieUpdate', [ $keyUserId ] );

		$this->setCookie( $keyUserId, [], $setCookieParam );

		$keyPermission = getUserGroupField( 'permission' );
		$isJson = getComponents( 'common' )->valid_json( $user[ $keyPermission ] );

		$user[ $keyPermission ] = $isJson ? json_decode( $user[ $keyPermission ], true ) : [];

		return $user;
	}

	/** End-CookieHandler private function */

	public function setCookie ( int $userId, array $updateData = [], string $logError = null ) : void
	{
		if ( ! getConfig()->useRememberMe )
		{
			return;
		}

		$common = getComponents( 'common' );
		if ( $userId <= 0 )
		{
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception( $common->lang( 'Validation.greater_than', $errArg ), 1 );
		}

		$isAssocData = $common->isAssocArray( $updateData );

		if ( ! empty( $updateData ) && ! $isAssocData )
		{
			throw new \Exception( $common->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$selector = bin2hex( random_bytes( 8 ) );
		$token = bin2hex( random_bytes( 20 ) );

		$cookieValue = "{$selector}:{$token}";
		$data = [
			'selector' => $selector,
			'token' => hash( 'sha256', $token )
		];
		$data = array_merge( $data, $updateData );

		$updatedSuccess = baseInstance( 'Authentication' )
			->loggedInUpdateData( $userId, $data );

		if ( $updatedSuccess )
		{
			$cookieComponent = getComponents( 'cookie' );
			$cookie = getConfig( 'cookie' );
			$ttl = time() + $cookie->ttl;
			$cookieComponent->set_cookie( $cookie->cookie, $cookieValue, ( string ) $ttl );
		}
		else
		{
			$error = "{$userId} LoggedIn with remember-me, but update failed";
			$common->log_message( 'error', $error );
		}
	}
}