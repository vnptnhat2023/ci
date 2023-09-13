<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    getInstance
};

class CookieHandle
{
	use TraitSingleton;

	public function regenerateCookie () : void
	{
		$cookieValue = password_hash( session_id(), PASSWORD_DEFAULT );
		$ttl = ( string ) getConfig( 'session' ) ->sessionTimeToUpdate;
		$cookieName = getConfig( 'cookie' ) ->cookie . '_test';

		getComponents( 'cookie' )->set_cookie( $cookieName , $cookieValue, $ttl );
	}

	public function cookieHandler () : bool
	{
		if ( ! getConfig()->useRememberMe )
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

		$user = getComponents( 'user' )
			->getUserWithGroup( getConfig( 'sql' )->getColumString(), [ 'selector' => $selector ] );

		if ( empty( $user ) )
		{
			return $incorrectCookie();
		}

		$isValid = hash_equals( $user[ 'token' ], hash( 'sha256', $token ) );
		$isUserIp = $user[ 'last_login' ] == getComponents( 'request' )->getIPAddress();

		if ( ! $isValid || ! $isUserIp )
		{
			return $incorrectCookie();
		}

		# Check status
		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) )
		{
			getInstance( Message::class ) ->denyStatus( $user[ 'status' ], false, false );
			return $incorrectCookie();
		}

		# @Todo: declare inside the config file: is using this feature
		$isMultiLogin = getInstance( Authentication::class ) ->isMultiLogin( $user[ 'session_id' ] );

		if ( ! $isMultiLogin )
		{
			getInstance( Message::class )->denyMultiLogin( true, [], false );
			return false;
		}

		# Refresh cookie
		$this->setCookie(
			(int) $user[ 'id' ],
			[],
			getComponents( 'common' )->lang('errorCookieUpdate', [ $user[ 'id' ] ])
		);

		$isJson = getComponents( 'common' )->valid_json( $user[ 'permission' ] );

		$user[ 'permission' ] = $isJson
			? json_decode( $user[ 'permission' ], true )
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