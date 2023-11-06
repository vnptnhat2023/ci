<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorParameterException;
use Red2Horse\Facade\Validation\ValidationFacadeInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Event\eventReturnedData;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setErrorWithLang;
use function Red2Horse\Mixins\Functions\Message\setSuccessWithLang;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Password\getHashPass;
use function Red2Horse\Mixins\Functions\Sql\getUserField;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetAttempts;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetTypes;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsLimited;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsSupported;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ResetPassword
{
	use TraitSingleton;

	private		?string 	$username;
	private		?string 	$email;
	private		?string 	$captcha;

	private function __construct () {}

	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool
	{
		if ( null === $u )
		{
			return false;
		}
		
		$authen = getBaseInstance( Authentication::class );
		
		if ( $authen->isLogged() || $authen->isLogged( true ) )
		{
			$this->alreadyLoggedIn( $authen->getUserdata() );
			return true;
		}
		
		helpers( 'throttle' );
		
		if ( throttleIsLimited( true ) )
		{
			return false;
		}

		return $this->forgetHandle( $u, $e, $c );
	}

	public function alreadyLoggedIn ( array $userData )
	{
		helpers( 'message' );
		setSuccessWithLang( true, 'successLogin', ( array ) $userData[ getUserField( 'username' ) ] );
	}

	/**
	 * Not using...
	 */
	public function resetPasswordSuccess ( array $userData ) : void
	{
		if ( ! $userEmail = explode( '@', $userData[ getUserField( 'email' ) ] ) )
		{
			$errorLog = sprintf( 'Email: "%s" is invalid', $userEmail[ 0 ] );
			getComponents( 'common' )->log_message( 'error', sprintf( 'Email: "%s" is invalid', $userEmail[ 0 ] ) );
			throw new ErrorParameterException( $errorLog );
		}

		$email = str_repeat( '*', strlen( $userEmail[ 0 ] ) ) . '@' . $userEmail[ 1 ];
		helpers( 'message' );
		$successArgs = [ $userData[ getUserField( 'username' ) ], $email ];
		setSuccessWithLang( true, 'successResetPassword', $successArgs );
	}

	private function captchaRegister () : bool
	{
		helpers( 'event' );

		if ( ! throttleIsSupported() )
		{
			return false;
		}

		$captcha = 'resetpassword_show_captcha_condition';
		[ $captcha => $showCaptchaCondition ] = eventReturnedData( 
			$captcha, throttleGetAttempts(), throttleGetTypes()
		);
		$showCaptchaCondition = ( bool ) ( $showCaptchaCondition ?? false );

		return $showCaptchaCondition;
	}

	public function forgetHandle ( string $u = null, string $e = null, string $c = null ) : bool
	{
		$this->username 	= $u;
		$this->email 		= $e;
		$this->captcha 		= $c;

		/** @var \Red2Horse\Config\Validation 	$configValidation */
		$configValidation 						= getConfig( 'validation' );
		/** @var ValidationFacadeInterface 		$validationComponent */
		$validationComponent 					= getComponents( 'validation' );

		$validateUserField 			= [
			$configValidation->user_username,
			$configValidation->user_email
		];
		$validateUserFieldWithCaptcha = [
			$configValidation->user_username,
			$configValidation->user_email,
			$configValidation->user_captcha
		];

		$showCaptchaCondition = $this->captchaRegister();

		$groups = $showCaptchaCondition ? $validateUserFieldWithCaptcha : $validateUserField;
		$rules 	= $validationComponent->getRules( $groups );
		$data 	= [
			$configValidation->user_username 	=> $this->username,
			$configValidation->user_email 		=> $this->email
		];

		helpers( [ 'message', 'model', 'password' ] );

		if ( ! $validationComponent->isValid( $data, $rules ) )
		{
			$errorMessage = [
				...array_values( $validationComponent->getErrors() ),
				getComponents( 'common' )->lang( 'Red2Horse.errorIncorrectInformation' )
			];
			setErrorMessage( $errorMessage, true );
			return false;
		}

		$userData = model( 'User/UserModel' )->first( $data );
		if ( [] === $userData )
		{
			setErrorWithLang( 'errorIncorrectInformation', [], true );
			return false;
		}

		$common 	= getComponents( 'common' );
		$randomPw 	= getHashPass( $common->random_string() );
		$editSet 	= [ getUserField( 'username' ) 	=> $userData[ getUserField( 'username' ) ] ];
		$editWhere 	= [ getUserField( 'password' ) 	=> $randomPw ];
		$error 		= 'Cannot update user password';

		if ( ! model( 'User/UserModel' )->edit( $editSet, $editWhere ) )
		{
			setErrorMessage( $error );
			return false;
		}

		if ( ! getBaseInstance( Notification::class )->mailSender( $randomPw ) )
		{
			setErrorMessage( $error );

			$errorLog = sprintf( 'Cannot sent email: %s', $userData[ getUserField( 'username' ) ] );
			$common->log_message( 'error' , $errorLog );

			return false;
		}

		setSuccessWithLang( true, 'successResetPassword' );

		return true;
	}
}
