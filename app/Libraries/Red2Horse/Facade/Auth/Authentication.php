<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    getHashPass,
    getInstance,
    getRandomString,
    getTable,
	getField,
    getUserField,
    getUserGroupField
};

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Authentication
{
	use TraitSingleton;

	private static ?string $username = null;
	private static ?string $password = null;
	private static ?string $captcha = null;
	private static bool $rememberMe = false;

	private function __construct () {}

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool
	{
		self::$username = $u;
		self::$password = $p;
		self::$rememberMe = $r;
		self::$captcha = $c;

		return getInstance( Utility::class )->typeChecker( 'login', $u, $p, null, $c );
	}

	public function logout () : bool
	{
		$message = getInstance( Message::class );
		$common = getComponents( 'common' );
		$session = getComponents( 'session' );
		$message::$successfully = true;

		getComponents( 'cookie' )->delete_cookie( getConfig( 'cookie' )->cookie );

		if ( $session->has( getConfig('session')->session ) )
		{
			$session->destroy();
			$message::$success[] = $common->lang( 'Red2Horse.successLogout' );
			return true;
		}

		$error = $common ->lang( 'Red2Horse.errorNeedLoggedIn') .
			$common ->lang( 'Red2Horse.homeLink');

		$message::$errors[] = $error;

		return false;
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		if ( ! $this->isLogged() )
		{
			return false;
		}

		$session = getComponents( 'session' );
		$userData = $session->get( getConfig( 'session' )->session );

		return empty( $key ) ? $userData : $userData[ $key ] ?? null;
	}

	/**
	 * Check cookie, session: when have cookie will set session
	 * @return boolean
	 */
	public function isLogged ( bool $withCookie = false ) : bool
	{
		if ( getComponents( 'session' )->has( getConfig( 'session' )->session ) )
		{
			return true;
		}

		if ( $withCookie )
		{
			return getInstance( CookieHandle::class )->cookieHandler();
		}

		return false;
	}

	private function loginInvalid ()
	{
		$validationComponent = getComponents( 'validation' );
		$configValidation = getConfig( 'validation' );

		if ( getComponents( 'throttle' )->showCaptcha() )
		{
			$data = [
				$configValidation->user_username => self::$username,
				$configValidation->user_password => self::$password,
				$configValidation->user_captcha => self::$captcha
			];

			$ruleCaptcha = [
				$configValidation->user_captcha => $validationComponent->getRules( $configValidation->user_captcha )
			];

			if ( ! $validationComponent->isValid( $data, $ruleCaptcha ) )
			{
				$errorCaptcha = $validationComponent->getErrors( $configValidation->user_captcha );
				return getInstance( Message::class )->errorInformation( true, $errorCaptcha );
			}
		}

		$incorrectInfo = false;
		$ruleUsername = [ $configValidation->user_username => $validationComponent->getRules( 'username' ) ];
		$data = [ $configValidation->user_username => self::$username ];

		if ( ! $validationComponent->isValid( $data, $ruleUsername ) )
		{
			$validationComponent->reset();
			$ruleEmail = [ $configValidation->user_username => $validationComponent->getRules( 'email' ) ];
			$incorrectInfo = ! $validationComponent->isValid( $data, $ruleEmail );
		}

		! $incorrectInfo ?: getInstance( Message::class )->errorInformation( true );

		return $incorrectInfo;
	}

	private function loginAfterValidation () : array
	{
		$userDataArgs = [
			sprintf( '%s.%s', getTable( 'user'), getUserField( 'username' ) ) => self::$username,
			sprintf( '%s.%s', getTable( 'user'), getUserField( 'email' ) ) => self::$username
		];

		$userData = getComponents( 'user' )->getUserWithGroup(
			\Red2Horse\Mixins\Functions\sqlSelectColumns( [ getUserField( 'password' ) ] ),
			$userDataArgs
		);

		$message = getInstance( Message::class );

		if ( empty( $userData ) )
		{
			return [ 'error' => $message->errorInformation() ];
		}

		$verifyPassword = getInstance( Password::class )
			->getVerifyPass( self::$password, $userData[ getUserField( 'password' ) ] );

		if ( ! $verifyPassword )
		{
			return [ 'error' => $message->errorInformation() ];
		}

		if ( 'active' !== $userData[ getUserField( 'status' ) ] )
		{
			return [ 'error' => $message->errorAccountStatus( $userData[ getUserField( 'status' ) ] ) ];
		}

		if ( ! $this->isMultiLogin( $userData[ getUserField( 'session_id' ) ] ) )
		{
			$message->errorMultiLogin( true, [], false );
			return [ 'error' => false ];
		}

		unset( $userData[ getUserField( 'password' ) ] );

		$isValidJson = getComponents( 'common' )
			->valid_json( $userData[ getUserGroupField( 'permission' ) ] );
		$userData[ getUserGroupField( 'permission' ) ] = ( $isValidJson )
			? json_decode( $userData[ getUserGroupField( 'permission' ) ], true )
			: [];

		return $userData;
	}

	public function loginHandler () : bool
	{
		if ( $this->loginInvalid() )
		{
			return false;
		}

		$userData = $this->loginAfterValidation();

		if ( array_key_exists( 'error', $userData ) )
		{
			return false;
		}

		/** Set response success to true */
		$this->setLoggedInSuccess( $userData );

		$userData[ getUserField( 'id' ) ] = ( int ) $userData[ getUserField( 'id' ) ];
		$userId = $userData[ getUserField( 'id' ) ];

		if ( getComponents( 'cache' )->isSupported() )
		{
			$this->roleHandle( $userData );
		}
		else
		{
			getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
		}

		/** Set cookie */
		if ( self::$rememberMe )
		{
			getInstance( CookieHandle::class )->setCookie( $userId );
		}

		/** Sql update */
		if ( ! $this->loggedInUpdateData( $userId ) )
		{
			getComponents( 'common' )
				->log_message( 'error', "{ $userId } Logged-in, but update failed" );
		}

		/** Generate cookie */
		getInstance( CookieHandle::class )->regenerateCookie();

		/** Clean old throttle attempts */
		getComponents( 'throttle' )->cleanup();

		return true;
	}

	private function roleHandle ( array $userData ) : void
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
				if ( ! $this->loggedInUpdateData( $userId, $updateGroupData, getTable( 'user_group' ) ) )
				{
					getComponents( 'common' )
						->log_message( 'error', "{ $userId } Logged-in, but update failed" );

					throw new \Error( 'Authentication cannot updated.', 406 );
				}
			}

			getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
		// }
	}

	public function setLoggedInSuccess ( array $userData ) : void
	{
		$message = getInstance( Message::class );
		$message::$successfully = true;
		$message::$success[] = getComponents( 'common' )
			->lang( 'Red2Horse.successLoggedWithUsername', [ $userData[ getUserField( 'username' ) ] ] );
	}

	public function isMultiLogin ( ?string $session_id = null ) : bool
	{
		if ( ! getConfig()->useMultiLogin )
		{
			return true;
		}

		$session = getComponents( 'session' );
		$pathFile = $session->sessionSavePath;
		$pathFile .= '/' . $session->sessionCookieName . $session_id;
		$date = getComponents( 'common' )->get_file_info( $pathFile, 'date' );

		if ( empty( $date ) )
		{
			return true;
		}

		$cookieName = $session->sessionCookieName . '_test';

		if ( $hash = getComponents( 'cookie' ) ->get_cookie( $cookieName ) )
		{
			if ( password_verify( $session_id, $hash ) )
			{
				return true;
			}

			getComponents( 'cookie' ) ->delete_cookie( $cookieName );

			return false;
		}

		$time = ( time() - $date[ 'date' ] );
		$sessionExp = ( int ) $session->sessionExpiration;

		if ( $sessionExp > 0 )
		{
			return $sessionExp > $time;
		}

		if ( $sessionExp === 0 )
		{
			return $session->sessionTimeToUpdate > $time;
		}

		getInstance( Message::class )::$errors[] = 'else';

		return false;
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	public function loggedInUpdateData ( int $userId, array $updateData = [], ?string $tableArg = 'user' ) : bool
	{
		$common = getComponents( 'common' );

		if ( $userId <= 0 )
		{
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception(
				$common->lang( 'Validation.greater_than', $errArg ),
				1
			);
		}

		$isAssocData = $common->isAssocArray( $updateData );

		if ( ! empty( $updateData ) && ! $isAssocData )
		{
			throw new \Exception( $common->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		if ( null !== $tableArg )
		{
			if ( $tableArg == getTable( 'user' ) )
			{
				$data = [
					getUserField( 'last_login' ) 	=> getComponents( 'request' )->getIPAddress(),
					getUserField( 'last_activity' ) => date( 'Y-m-d H:i:s' ),
					getUserField( 'session_id' ) 	=> session_id()
				];

				$data = array_merge( $data, $updateData );

				return getComponents( 'user' )->updateUser( $userId, $data );
			}

			if ( empty( $updateData ) )
			{
				return false;
			}

			if ( $tableArg == getTable( 'user_group' ) )
			{
				return getComponents( 'user' )->updateUserGroup( $userId, $updateData );
			}
		}

		return false;
	}
}