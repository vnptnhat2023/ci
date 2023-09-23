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
    getRandomString
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
		$cookie = getComponents( 'cookie' );
		$message::$successfully = true;

		$cookie->delete_cookie( getConfig( 'cookie' )->cookie );

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
		$validation = getComponents( 'validation' );
		$configValidation = getConfig( 'validation' );

		if ( getComponents( 'throttle' )->showCaptcha() )
		{
			$data = [
				$configValidation::$username => self::$username,
				$configValidation::$password => self::$password,
				$configValidation::$captcha => self::$captcha
			];

			$ruleCaptcha = [
				$configValidation::$captcha => $validation->getRules( $configValidation::$captcha )
			];

			if ( ! $validation->isValid( $data, $ruleCaptcha ) )
			{
				$errorCaptcha = $validation->getErrors( $configValidation::$captcha );
				return getInstance( Message::class )->errorInformation( true, $errorCaptcha );
			}
		}

		$incorrectInfo = false;
		$ruleUsername = [ $configValidation::$username => $validation->getRules( 'username' ) ];
		$data = [ $configValidation::$username => self::$username ];

		if ( ! $validation->isValid( $data, $ruleUsername ) )
		{
			$validation->reset();
			$ruleEmail = [ $configValidation::$username => $validation->getRules( 'email' ) ];
			$incorrectInfo = ! $validation->isValid( $data, $ruleEmail );
		}

		! $incorrectInfo ?: getInstance( Message::class )->errorInformation( true );

		return $incorrectInfo;
	}

	private function loginAfterValidation () : array
	{
		$userDataArgs = [
			'user.username' => self::$username,
			'user.email' => self::$username
		];

		$userData = getComponents( 'user' )->getUserWithGroup(
			\Red2Horse\Mixins\Functions\sqlGetColumns( [ 'password' ] ),
			$userDataArgs
		);

		$message = getInstance( Message::class );

		if ( empty( $userData ) )
		{
			return [ 'error' => $message->errorInformation() ];
		}

		$verifyPassword = getInstance( Password::class )
			->getVerifyPass( self::$password, $userData[ 'password' ] );

		if ( ! $verifyPassword )
		{
			return [ 'error' => $message->errorInformation() ];
		}

		if ( 'active' !== $userData[ 'status' ] )
		{
			return [ 'error' => $message->errorAccountStatus( $userData['status'] ) ];
		}

		if ( ! $this->isMultiLogin( $userData[ 'session_id' ] ) )
		{
			$message->errorMultiLogin( true, [], false );
			return [ 'error' => false ];
		}

		unset( $userData[ 'password' ] );

		$isValidJson = getComponents( 'common' )->valid_json( $userData[ 'permission' ] );
		$userData[ 'permission' ] = ( $isValidJson )
			? json_decode( $userData[ 'permission' ], true )
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

		# --- Set response success to true
		$this->setLoggedInSuccess( $userData );

		# --- Set session
		if ( ! getComponents( 'common' )->valid_json( $userData[ 'role' ] ) )
		{
			throw new \Error( 'Role: Invalid json format !', 406 );
		}

		$roleJson = json_decode( $userData[ 'role' ], true );
		
		if ( ! array_key_exists( 'role', $roleJson ) || ! array_key_exists( 'hash', $roleJson ) )
		{
			throw new \Error( 'Role: Invalid json format !', 406 );
		}

		$roleString = $roleJson[ 'role' ];
		$roleHash = getRandomString( $roleString );
		$roleData = [ 'role' => $roleString, 'hash' => $roleHash ];
		$userData[ 'role' ] = $roleData;

		$session = getComponents( 'session' );
		$session->set( getConfig( 'session' )->session, $userData );

		# --- Set cookie
		$userId = ( int ) $userData[ 'id' ];

		if ( self::$rememberMe )
		{
			getInstance( CookieHandle::class )->setCookie( $userId );
		}

		$roleDB = [ 'role' => $roleString, 'hash' => getHashPass( $roleHash ) ];
		$updateGroupData = [ 'role' => json_encode( $roleDB ) ];

		if ( ! $this->loggedInUpdateData( $userId, $updateGroupData, 'user_group' ) )
		{
			getComponents( 'common' )
				->log_message( 'error', "{ $userId } Logged-in, but update failed" );
		}

		if ( ! $this->loggedInUpdateData( $userId ) )
		{
			getComponents( 'common' )
				->log_message( 'error', "{ $userId } Logged-in, but update failed" );
		}

		getInstance( CookieHandle::class )->regenerateCookie();
		# --- End cookie set

		# --- Clean old throttle attempts
		getComponents( 'throttle' )->cleanup();

		return true;
	}

	public function setLoggedInSuccess ( array $userData ) : void
	{
		$message = getInstance( Message::class );
		$message::$successfully = true;
		$message::$success[] = getComponents( 'common' )
			->lang( 'Red2Horse.successLoggedWithUsername', [ $userData[ 'username' ] ] );
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
			$configSql = getConfig( 'Sql' );
			$tables = $configSql->tables[ 'tables' ];

			if ( $tableArg == $tables[ 'user'] )
			{
				$data = [
					'last_login' => getComponents( 'request' )->getIPAddress(),
					'last_activity' => date( 'Y-m-d H:i:s' ),
					'session_id' => session_id()
				];
				$data = array_merge( $data, $updateData );

				return getComponents( 'user' )->updateUser( $userId, $data );
			}

			if ( empty( $updateData ) )
			{
				return false;
			}

			if ( $tableArg == $tables[ 'user_group' ] )
			{
				return getComponents( 'user' )->updateUserGroup( $userId, $updateData );
			}
			// return getComponents( $table )->updateUser( $userId, $updateData );
		}

		return false;
	}
}