<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Config\Validation;
use Red2Horse\Exception\ErrorValidationException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;
use Red2Horse\Facade\Validation\ValidationFacadeInterface;
use Red2Horse\Mixins\Traits\Object\TraitInstanceTrigger;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Event\eventReturnedData;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Message\getMessageInstance;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setErrorWithLang;
use function Red2Horse\Mixins\Functions\Message\setSuccessWithLang;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Password\getVerifyPass;
use function Red2Horse\Mixins\Functions\Sql\
{
    getUserField,
    getUserGroupField
};
use function Red2Horse\Mixins\Functions\Throttle\throttleCleanup;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetAttempts;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetTypes;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsLimited;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsSupported;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Authentication
{
	use TraitSingleton, TraitInstanceTrigger;

	private		?string		$username = null;
	private		?string		$password = null;
	private		?string		$captcha = null;
	private		bool		$rememberMe = false;

	private function __construct () {}

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool
	{
		if ( null === $u )
		{
			return false;
		}

		if ( $this->isLogged() || $this->isLogged( true ) )
		{
			$this->successWithMessage( $this->getUserdata() );
			return true;
		}

		helpers( 'throttle' );
		if ( throttleIsLimited( true ) )
		{
			return false;
		}

		$this->username 	= $u;
		$this->password 	= $p;
		$this->rememberMe 	= $r;
		$this->captcha 		= $c;

		return $this->loginHandle();
	}

	public function logout () : bool
	{
		$common = getComponents( 'common' );
		$session = getComponents( 'session' );
		helpers( 'message' );

		getComponents( 'cookie' )->delete_cookie( getConfig( 'cookie' )->cookie );

		if ( $session->has( getConfig( 'session' )->session ) )
		{
			$session->destroy();
			setSuccessWithLang( true, 'successLogout' ); 
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
	 * @param array|string|null $keys
	 * @return mixed
	 */
	public function getUserdata ( $keys = null )
	{
		return getBaseInstance( 'SessionHandle' )->getSession( $keys );
	}

	/**
	 * @return boolean
	 */
	public function isLogged ( bool $withCookie = false ) : bool
	{
		return getBaseInstance( SessionHandle::class )->isLogged( $withCookie );
	}

	private function captchaRegister () : bool
	{
		helpers( [ 'event', 'throttle' ] );
		
		if ( ! throttleIsSupported() )
		{
			return false;
		}

		$captcha = 'authentication_show_captcha_condition';
		[ $captcha => $showCaptchaCondition ] = eventReturnedData( 
			$captcha, throttleGetAttempts(), throttleGetTypes()
		);

		$showCaptchaCondition = ( bool ) ( $showCaptchaCondition ?? false );

		return $showCaptchaCondition;
	}

	private function formValidate () : bool
	{
		/** @var ValidationFacadeInterface $validationComponent */
		$validationComponent = getComponents( 'validation' );

		/** @var Validation $configValidation */
		$configValidation 	= getConfig( 'validation' );
		$keyUsername 		= getUserField( 'username' );
		$keyEmail 	 		= getUserField( 'email' );
		$keyPassword 		= getUserField( 'password' );
		$keyCaptcha  		= $configValidation->user_captcha;

		helpers( 'message' );

		if ( $this->captchaRegister() )
		{
			$data = [
				$keyUsername => $this->username,
				$keyPassword => $this->password,
				$keyCaptcha	 => $this->captcha
			];

			$ruleCaptcha = [
				$keyCaptcha => $validationComponent->getRules( $keyCaptcha )
			];
			
			if ( ! $validationComponent->isValid( $data, $ruleCaptcha ) )
			{
				$errorValidation = $validationComponent->getErrors( $keyCaptcha );
				setErrorMessage( $errorValidation, true );
				return false;
			}
		}

		$ruleUsername 	= [
			$keyUsername => $validationComponent->getRules( $keyUsername ),
			$keyPassword => $validationComponent->getRules( $keyPassword )
		];
		$data 			= [
			$keyUsername => $this->username,
			$keyPassword => $this->password
		];

		if ( ! $validationComponent->isValid( $data, $ruleUsername ) )
		{
			$ValidationErrors = [
				getComponents( 'common' )->lang( 'Red2Horse.errorIncorrectInformation' ),
				...array_values( $validationComponent->getErrors() )
			];

			/** Reset validation */
			$validationComponent->reset();
			$ruleEmail = [
				$keyUsername => $validationComponent->getRules( $keyEmail ),
				$keyPassword => $validationComponent->getRules( $keyPassword )
			];

			if ( $validationComponent->isValid( $data, $ruleEmail ) )
			{
				return true;
			}

			$ValidationErrors = [
				getComponents( 'common' )->lang( 'Red2Horse.errorIncorrectInformation' ), 
				...array_values( $validationComponent->getErrors() )
			];

			setErrorMessage( $ValidationErrors, true );
			
			return false;
		}

		return true;
	}

	public function statusValidate ( string $status ) : bool
	{
		/** @var array $userStatusList */
		$userStatusList 	= getUserField( 'status_list' );

		if ( ! in_array( $status, array_values( $userStatusList ) ) )
		{
			return false;
		}

		$messageInstance 						= getMessageInstance();
		$messageInstance::$hasBanned 			= ( $status === $userStatusList[ 'banned' ] );
		$messageInstance::$accountInactive 		= ( $status === $userStatusList[ 'inactive' ] );

		return $status === $userStatusList[ 'active' ];
	}

	/** @return bool|array */
	private function formData ()
	{
		helpers( [ 'message', 'model' ] );

		$userData = model( 'User/UserModel' )->first( [
			'user.username' => $this->username,
			'user.email' 	=> $this->username,
		] );

		if ( empty( $userData ) )
		{
			setErrorWithLang(  'errorIncorrectInformation', [], true );
			return false;
		}

		helpers( 'password' );
		$verifyPassword = getVerifyPass( $this->password, $userData[ getUserField( 'password' ) ] );

		if ( ! $verifyPassword )
		{
			setErrorWithLang(  'errorIncorrectInformation', [], true );
			return false;
		}

		if ( ! $this->statusValidate( $userStatus = $userData[ getUserField( 'status' ) ] ) )
		{
			setErrorWithLang(  'errorNotReadyYet', [ $userStatus ], true );
			return false;
		}

		if ( ! $this->isMultiLogin( $userData[ getUserField( 'session_id' ) ] ) )
		{
			setErrorWithLang(  'noteLoggedInAnotherPlatform', [], true );
			return false;
		}

		unset( $userData[ getUserField( 'password' ) ] );

		$permissionKey 	= getUserGroupField( 'permission' );
		$isValidJson 	= getComponents( 'common' )->valid_json( $userData[ $permissionKey ] );

		$userData[ $permissionKey ] = ( $isValidJson )
			? json_decode( $userData[ $permissionKey ], true )
			: [];

		return $userData;
	}

	public function loginHandle () : bool
	{
		if ( ! $this->formValidate() )
		{
			return false;
		}

		if ( false === $userData = $this->formData() )
		{
			return false;
		}

		/** Set response success to true */
		$this->successWithMessage( $userData );

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
		if ( $this->rememberMe )
		{
			getBaseInstance( CookieHandle::class )->setCookie( $userId );
		}

		/** Sql update */
		if ( ! $this->update( $userId ) )
		{
			$errorLog = sprintf('user-id: "%s" logged-in, but failed update', $userId );
			getComponents( 'common' )->log_message( 'error', $errorLog );
		}

		/** Generate cookie */
		getBaseInstance( CookieHandle::class )->regenerateCookie();

		/** Clean old throttle attempts */
		helpers( [ 'throttle' ] );
		if ( throttleIsSupported() )
		{
			throttleCleanup();
		}

		return true;
	}

	public function successWithMessage ( array $userData ) : void
	{
		helpers( 'message' );
		setSuccessWithLang( true, 'successLogin', ( array ) $userData[ getUserField( 'username' ) ] );
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

		helpers( [ 'message' ] );
		setErrorMessage( 'else' );

		return false;
	}

	/**
	 * @throws ErrorValidationException
	 * @return boolean
	 */
	public function update ( int $userId ) : bool
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

		$data = [
			getUserField( 'last_login' ) 	=> getComponents( 'request' )->getIPAddress(),
			getUserField( 'last_activity' ) => date( 'Y-m-d H:i:s' ),
			getUserField( 'session_id' ) 	=> session_id()
		];
		$where = [ 'user.id' => $userId ];

		return ( bool ) model( 'User/UserModel' )->edit( $data, $where );
	}
}