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

class Authentication
{
	use TraitSingleton;

	private static ?string $username = null;
	private static ?string $password = null;
	private static ?string $captcha = null;
	private static bool $rememberMe = false;

	public function __construct()
	{

	}

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

		getComponents( 'cookie' ) ->delete_cookie( getConfig( 'cookie' )->cookie );

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

		if ( empty( $key ) )
		{
			return getComponents( 'session' )->get( getConfig( 'session' )->session );
		}

		$userData = getComponents( 'session' )->get( getConfig( 'session' )->session );

		return $userData[ $key ] ?? null;
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
				$configValidation::USERNAME => self::$username,
				$configValidation::PASSWORD => self::$password,
				$configValidation::CAPTCHA => self::$captcha
			];

			$ruleCaptcha = [
				$configValidation::CAPTCHA => $validation->getRules( $configValidation::CAPTCHA )
			];

			if ( ! $validation->isValid( $data, $ruleCaptcha ) )
			{
				$errorCaptcha = $validation->getErrors( $configValidation::CAPTCHA );
				return getInstance( Message::class )->incorrectInfo( true, $errorCaptcha );
			}
		}

		$incorrectInfo = false;
		$ruleUsername = [ $configValidation::USERNAME => $validation->getRules( 'username' ) ];
		$data = [ $configValidation::USERNAME => self::$username ];

		if ( ! $validation->isValid( $data, $ruleUsername ) )
		{
			$validation->reset();
			$ruleEmail = [ $configValidation::USERNAME => $validation->getRules( 'email' ) ];
			$incorrectInfo = ! $validation->isValid( $data, $ruleEmail );
		}

		! $incorrectInfo ?: getInstance( Message::class )->incorrectInfo( true );

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

		if ( empty( $userData ) )
		{
			return [ 'error' => getInstance( Message::class )->incorrectInfo() ];
		}

		$verifyPassword = Password::getInstance()
			->getVerifyPass( self::$password, $userData[ 'password' ] );

		if ( ! $verifyPassword )
		{
			return [ 'error' => getInstance( Message::class )->incorrectInfo() ];
		}

		if ( 'active' !== $userData[ 'status' ] )
		{
			return [ 'error' => getInstance( Message::class )->denyStatus( $userData['status'] ) ];
		}

		if ( ! $this->isMultiLogin( $userData[ 'session_id' ] ) )
		{
			getInstance( Message::class )->denyMultiLogin( true, [], false );
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
		getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );

		# --- Set cookie
		$userId = ( int ) $userData[ 'id' ];

		if ( self::$rememberMe )
		{
			getInstance( CookieHandle::class )->setCookie( $userId );
		}
		else if ( ! $this->loggedInUpdateData( $userId ) )
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

		$pathFile = getConfig( 'session' )->sessionSavePath;
		$pathFile .= '/' . getConfig( 'session' )->sessionCookieName . $session_id;
		$date = getComponents( 'common' )->get_file_info( $pathFile, 'date' );

		if ( empty( $date ) )
		{
			return true;
		}

		$cookieName = getConfig( 'session' )->sessionCookieName . '_test';

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
		$sessionExp = ( int ) getConfig( 'session' )->sessionExpiration;

		if ( $sessionExp > 0 )
		{
			return $sessionExp > $time;
		}

		if ( $sessionExp === 0 )
		{
			return getConfig( 'session' )->sessionTimeToUpdate > $time;
		}

		getInstance( Message::class )::$errors[] = 'else';

		return false;
	}

	/**
	 * @throws \Exception
	 * @return boolean
	 */
	public function loggedInUpdateData ( int $userId, array $updateData = [] )
	{
		if ( $userId <= 0 )
		{
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception(
				getComponents( 'common' )->lang( 'Validation.greater_than', $errArg ),
				1
			);
		}

		$isAssocData = getComponents( 'common' )->isAssocArray( $updateData );

		if ( ! empty( $updateData ) && ! $isAssocData )
		{
			throw new \Exception( getComponents( 'common' )->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$data = [
			'last_login' => getComponents( 'request' )->getIPAddress(),
			'last_activity' => date( 'Y-m-d H:i:s' ),
			'session_id' => session_id()
		];
		$data = array_merge( $data, $updateData );

		return getComponents( 'user' )->updateUser( $userId, $data );
	}
}