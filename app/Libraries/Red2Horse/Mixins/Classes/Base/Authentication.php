<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Config\Validation;
use Red2Horse\Exception\ErrorValidationException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;
use Red2Horse\Facade\Validation\ValidationFacadeInterface;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setSuccessMessage;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Sql\
{
    getTable,
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

		return getBaseInstance( Utility::class )->typeChecker( 'login', $u, $p, null, $c );
	}

	public function logout () : bool
	{
		$common = getComponents( 'common' );
		$session = getComponents( 'session' );

		getComponents( 'cookie' )->delete_cookie( getConfig( 'cookie' )->cookie );

		if ( $session->has( getConfig( 'session' )->session ) )
		{
			$session->destroy();
			setSuccessMessage( $common->lang( 'Red2Horse.successLogout' ) ); 
			return true;
		}

		$error = sprintf(
			'%s.%s', 
			$common ->lang( 'Red2Horse.errorNeedLoggedIn'),
			$common ->lang( 'Red2Horse.homeLink')
		);

		setErrorMessage( $error );

		return false;
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( ?string $key = null )
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
			return getBaseInstance( CookieHandle::class )->cookieHandler();
		}

		return false;
	}

	private function loginInvalid ()
	{
		/** @var ValidationFacadeInterface $validationComponent */
		$validationComponent = getComponents( 'validation' );

		/** @var Validation $configValidation */
		$configValidation 	= getConfig( 'validation' );
		$keyUsername 		= getUserField( 'username' );
		$keyEmail 	 		= getUserField( 'email' );
		$keyPassword 		= getUserField( 'password' );
		$keyCaptcha  		= $configValidation->user_captcha;

		if ( getComponents( 'throttle' )->showCaptcha() )
		{
			$data = [
				$keyUsername => self::$username,
				$keyPassword => self::$password,
				$keyCaptcha => self::$captcha
			];

			$ruleCaptcha = [
				$keyCaptcha => $validationComponent->getRules( $keyCaptcha )
			];

			if ( ! $validationComponent->isValid( $data, $ruleCaptcha ) )
			{
				$errorCaptcha = $validationComponent->getErrors( $keyCaptcha );
				return getBaseInstance( Message::class )->errorInformation( true, $errorCaptcha );
			}
		}

		$incorrectInfo 	= false;
		$ruleUsername 	= [ $keyUsername => $validationComponent->getRules( $keyUsername ) ];
		$data 			= [ $keyUsername => self::$username ];

		if ( ! $validationComponent->isValid( $data, $ruleUsername ) )
		{
			/** Reset validation */
			$validationComponent->reset();
			$ruleEmail 		= [ $keyUsername => $validationComponent->getRules( $keyEmail ) ];
			$incorrectInfo 	= ! $validationComponent->isValid( $data, $ruleEmail );
		}

		! $incorrectInfo ?: getBaseInstance( Message::class )->errorInformation( true );

		return $incorrectInfo;
	}

	private function loginAfterValidation () : array
	{
		$userData = model( 'User/UserModel' )->first( [
			'user.username' => self::$username,
			'user.email' 	=> self::$username,
		] );

		$message = getBaseInstance( Message::class );

		if ( empty( $userData ) )
		{
			return [ 'error' => $message->errorInformation() ];
		}

		$verifyPassword = getBaseInstance( Password::class )
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

		$permissionKey 	= getUserGroupField( 'permission' );
		$isValidJson 	= getComponents( 'common' )->valid_json( $userData[ $permissionKey ] );

		$userData[ $permissionKey ] = ( $isValidJson )
			? json_decode( $userData[ $permissionKey ], true )
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

		$userFieldID 				= getUserField( 'id' );
		$userData[ $userFieldID ] 	= ( int ) $userData[ $userFieldID ];
		$userId 					= $userData[ $userFieldID ];

		if ( getComponents( 'cache' )->isSupported() )
		{
			getBaseInstance( SessionHandle::class )->roleHandle( $userData );
		}
		else
		{
			getComponents( 'session' )->set( getConfig( 'session' )->session, $userData );
		}

		/** Set cookie */
		if ( self::$rememberMe )
		{
			getBaseInstance( CookieHandle::class )->setCookie( $userId );
		}

		/** Sql update */
		if ( ! $this->loggedInUpdateData( $userId ) )
		{
			$errorLog = sprintf('user-id: "%s" logged-in, but failed update', $userId );
			getComponents( 'common' )->log_message( 'error', $errorLog );
		}

		/** Generate cookie */
		getBaseInstance( CookieHandle::class )->regenerateCookie();

		/** Clean old throttle attempts */
		if ( getConfig( 'throttle' )->useThrottle )
		{
			getComponents( 'throttle' )->cleanup();
		}

		return true;
	}

	public function setLoggedInSuccess ( array $userData ) : void
	{
		setSuccessMessage( getComponents( 'common' )->lang(
			'Red2Horse.successLoggedWithUsername',
			[ $userData[ getUserField( 'username' ) ] ] 
		) );
	}

	public function isMultiLogin ( ?string $session_id = null ) : bool
	{
		if ( ! getConfig()->useMultiLogin )
		{
			return true;
		}

		$session 		= getComponents( 'session' );
		$pathFile 		= $session->sessionSavePath;
		$pathFile 		.= '/' . $session->sessionCookieName . $session_id;
		$date 			= getComponents( 'common' )->get_file_info( $pathFile, 'date' );

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

		setErrorMessage( 'else' );

		return false;
	}

	/**
	 * @throws ErrorValidationException
	 * @return boolean
	 */
	public function loggedInUpdateData ( int $userId, array $updateData = [], ?string $tableArg = null ) : bool
	{
		$common = getComponents( 'common' );

		if ( $userId <= 0 )
		{
			$errorValidation = $common->lang( 
				'Validation.greater_than', 
				[ 'field' => 'user_id', 'param' => $userId ]
			);
			throw new ErrorValidationException( $errorValidation, 406 );
		}

		$isAssocData = $common->isAssocArray( $updateData );

		if ( ! empty( $updateData ) && ! $isAssocData )
		{
			throw new ErrorValidationException( $common->lang( 'Red2Horse.isAssoc' ), 406 );
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

				$edit = model( 'User/UserModel' )->edit( $data, [ 'user.id' => $userId ] );
				return ( bool ) $edit;
			}

			if ( empty( $updateData ) )
			{
				return false;
			}

			if ( $tableArg == getTable( 'user_group' ) )
			{
				$edit = model( 'User/UserModel' )->edit( $updateData, [ 'user.id' => $userId ] );
				return ( bool ) $edit;
			}
		}

		return false;
	}
}