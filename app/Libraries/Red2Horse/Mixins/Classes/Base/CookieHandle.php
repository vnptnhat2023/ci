<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorValidationException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setErrorWithLang;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Sql\getUserField;
use function Red2Horse\Mixins\Functions\Sql\getUserGroupField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CookieHandle
{
	use TraitSingleton;

	private 	string 		$hash 		= 'sha256';

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
			getBaseInstance( 'SessionHandle' )->roleHandle( $user );
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
		$configCookieName = getConfig( 'cookie' )->cookie;
		getComponents( 'cookie' )->delete_cookie( $configCookieName );
		return false;
	}

	/** @return array|false */
	private function _getCookieConfig ()
	{
		if ( ! getConfig( 'BaseConfig' )->useRememberMe )
		{
			return false;
		}

		$configCookie 	= getConfig( 'cookie' );
		$cookie 		= getComponents( 'cookie' );
		$userCookie 	= $cookie->get_cookie( $configCookie->cookie );

		if ( empty( $userCookie ) || ! is_string( $userCookie ) )
		{
			return false;
		}

		$separate = explode( ':', $userCookie, 2 );

		if ( empty( $separate[ 0 ] ) || empty( $separate[ 1 ] ) )
		{
			return $this->_cleanOldCookie();
		}

		return [
			'selector' 	=> $separate[ 0 ], 
			'token' 	=> $separate[ 1 ]
		];
	}

	/**
	 * @return false|array
	 */
	private function _queryValidate ( $selector, $token )
	{
		helpers( 'model' );
		$user = model( 'User/UserModel' )->first( [ 'user.selector' => $selector ] );

		if ( [] === $user )
		{
			return $this->_cleanOldCookie();
		}

		/** Validate cookie */
		$isValid = hash_equals(
			$user[ getUserField( 'token' ) ],
			hash( 'sha256', $token )
		);

		$isUserIp = $user[ getUserField( 'last_login' ) ] == getComponents( 'request' )->getIPAddress();

		if ( ! $isValid || ! $isUserIp )
		{
			return $this->_cleanOldCookie();
		}

		return $user;
	}

	private function _userStatus ( array $userData ) : bool
	{
		$userStatus = $userData[ getUserField( 'status' ) ];
		if ( ! getBaseInstance( Authentication::class )->statusValidate( $userStatus ) )
		{
			helpers( 'message' );
			setErrorWithLang( 'errorNotReadyYet', [ $userStatus ] );
			return $this->_cleanOldCookie();
		}

		return true;
	}

	private function _userCheckMultiLogin ( array $user ) : bool
	{
		$userSessionId = $user[ getUserField( 'session_id' ) ];
		# @Todo: declare inside the config file: is using this feature
		$isMultiLogin = getBaseInstance( 'Authentication' )->isMultiLogin( $userSessionId );

		if ( ! $isMultiLogin )
		{
			helpers( 'message' );
			setErrorWithLang( 'noteLoggedInAnotherPlatform', [], true );
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
		$this->setCookie( ( int ) $user[ getUserField( 'id' ) ] );

		$keyPermission 			= getUserGroupField( 'permission' );
		$isJson 				= getComponents( 'common' )->valid_json( $user[ $keyPermission ] );

		$user[ $keyPermission ] = $isJson 
			? json_decode( $user[ $keyPermission ], true ) 
			: [];

		return $user;
	}
	/** End-CookieHandler private function */

	public function setCookie ( int $userId, array $updateData = [] ) : void
	{
		if ( ! getConfig()->useRememberMe )
		{
			return;
		}

		$common = getComponents( 'common' );
		if ( $userId <= 0 )
		{
			$errorLangArgs = [ 'field' => 'user_id', 'param' => $userId ];
			$errorValidation = $common->lang( 'Validation.greater_than', $errorLangArgs );
			throw new ErrorValidationException( $errorValidation );
		}

		if ( ! $common->isAssocArray( $updateData ) )
		{
			throw new ErrorValidationException( $common->lang( 'Red2Horse.isAssoc' ) );
		}

		$selector 		= bin2hex( random_bytes( 8 ) );
		$token 			= bin2hex( random_bytes( 20 ) );
		$cookieValue 	= "{$selector}:{$token}";
		$data 			= [
			'selector' 	=> $selector,
			'token' 	=> hash( 'sha256', $token )
		];
		$data 			= array_merge( $data, $updateData );

		if ( ! model( 'User/UserModel' )->edit( $data, [ 'user.id' => $userId ] ) )
		{
			$common->log_message( 'error', sprintf( 'Cookie update failed, userID: "%s"', $userId ) );
		}

		$cookieComponent 	= getComponents( 'cookie' );
		$cookie 			= getConfig( 'cookie' );
		$ttl 				= time() + $cookie->ttl;
		$cookieComponent->set_cookie( $cookie->cookie, $cookieValue, ( string ) $ttl );
	}
}