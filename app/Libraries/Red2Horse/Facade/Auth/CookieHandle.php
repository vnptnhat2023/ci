<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    getInstance,
    getTable,
    getUserField,
    getUserGroupField,
    selectExports
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CookieHandle
{
	use TraitSingleton;

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
		if ( ! getConfig( 'BaseConfig' )->useRememberMe )
		{
			return false;
		}

		$userCookie = getComponents( 'cookie' )->get_cookie( getConfig( 'cookie' )->cookie );

		if ( empty( $userCookie ) || ! is_string( $userCookie ) )
		{
			return false;
		}

		$separate = explode( ':', $userCookie, 2 );

		$incorrectCookie = function() : bool
		{
			getComponents( 'cookie' )->delete_cookie( getConfig( 'cookie' )->cookie );
			return false;
		};

		if ( empty( $separate[ 0 ] ) || empty( $separate[ 1 ] ) )
		{
			return $incorrectCookie();
		}

		$selector = $separate[ 0 ];
		$token = $separate[ 1 ];

		$user = getComponents( 'user' ) ->getUserWithGroup(
			selectExports( [ getTable( 'user' ) => [], getTable( 'user_group' ) => [] ] ),
			[ getUserField( 'selector' ) => $selector ]
		);

		if ( empty( $user ) )
		{
			return $incorrectCookie();
		}

		$isValid = hash_equals( $user[ getUserField( 'token' ) ], hash( 'sha256', $token ) );
		$isUserIp = $user[ getUserField( 'last_login' ) ] == getComponents( 'request' )->getIPAddress();

		if ( ! $isValid || ! $isUserIp )
		{
			return $incorrectCookie();
		}

		# Check status
		if ( in_array( $user[ getUserField( 'status' ) ] , [ 'inactive', 'banned' ] ) )
		{
			getInstance( Message::class )->errorAccountStatus(
				$user[ getUserField( 'status' ) ], false, false
			);
			return $incorrectCookie();
		}

		# @Todo: declare inside the config file: is using this feature
		$isMultiLogin = getInstance( Authentication::class ) ->isMultiLogin( $user[ getUserField( 'session_id' ) ] );

		if ( ! $isMultiLogin )
		{
			getInstance( Message::class )->errorMultiLogin( true, [], false );
			return false;
		}

		# Refresh cookie
		$this->setCookie(
			(int) $user[ getUserField( 'id' ) ],
			[],
			getComponents( 'common' )->lang('errorCookieUpdate', [ $user[ getUserField( 'id' ) ] ])
		);

		$isJson = getComponents( 'common' )->valid_json( $user[ getUserGroupField( 'permission' ) ] );

		$user[ getUserGroupField( 'permission' ) ] = $isJson
			? json_decode( $user[ getUserGroupField( 'permission' ) ], true )
			: [];

		getComponents( 'session' )->set( getConfig( 'session' )->session, $user );

		$this->regenerateCookie();

		return true;
	}

	public function setCookie ( int $userId, array $updateData = [], string $logError = null ) : void
	{
		if ( ! getConfig()->useRememberMe )
		{
			return;
		}

		if ( $userId <= 0 )
		{
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception( getComponents( 'common' )->lang( 'Validation.greater_than', $errArg ), 1 );
		}

		$isAssocData = getComponents( 'common' )->isAssocArray( $updateData );

		if ( ! empty( $updateData ) && ! $isAssocData )
		{
			throw new \Exception( getComponents( 'common' )->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$selector = bin2hex( random_bytes( 8 ) );
		$token = bin2hex( random_bytes( 20 ) );

		$cookieValue = "{$selector}:{$token}";
		$data = [
			'selector' => $selector,
			'token' => hash( 'sha256', $token )
		];
		$data = array_merge( $data, $updateData );

		$updatedSuccess = getInstance( Authentication::class ) ->loggedInUpdateData( $userId, $data );

		if ( $updatedSuccess )
		{
			$ttl = time() + getConfig( 'cookie' )->ttl;
			setcookie( getConfig( 'cookie' )->cookie, $cookieValue, $ttl, '/' );
		}
		else
		{
			$logErr = $logError ?: "{$userId} LoggedIn with remember-me, but update failed";
			getComponents( 'common' )->log_message( 'error', $logErr );
		}
	}
}