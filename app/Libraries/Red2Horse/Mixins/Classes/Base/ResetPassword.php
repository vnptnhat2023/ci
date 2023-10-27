<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorParameterException;
use Red2Horse\Facade\Validation\ValidationFacadeInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getBaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setSuccessMessage;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Sql\getUserField;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ResetPassword
{
	use TraitSingleton;

	private static ?string $username;
	private static ?string $email;
	private static ?string $captcha;

	private function __construct () {}

	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool
	{
		return getBaseInstance( Utility::class )
			->typeChecker( 'forget', $u, null, $e, $c );
	}

	public function alreadyLoggedIn ( array $userData )
	{
		$success = ( array ) getComponents( 'common' )
			->lang(
				'Red2Horse.successLoggedWithUsername',
				[ $userData[ getUserField( 'username' ) ] ]
			);

		setSuccessMessage( $success );
	}

	/**
	 * Not using...
	 */
	public function resetPasswordSuccess ( array $userData ) : void
	{
		if ( ! $userEmail = explode( '@', $userData[ getUserField( 'email' ) ] ) )
		{
			$errorLog = sprintf( 'Email: "%s" is invalid', $userEmail[ 0 ] );
			getComponents( 'common' )->log_message( 'error', $errorLog );

			throw new ErrorParameterException( $errorLog );
		}

		$email = str_repeat( '*', strlen( $userEmail[ 0 ] ) ) . '@' . $userEmail[ 1 ];

		$success = ( array ) getComponents( 'common' )
			->lang(
				'Red2Horse.successResetPassword',
				[ $userData[ getUserField( 'username' ) ], $email ]
			);

		setSuccessMessage( $success );
	}

	public function forgetHandler ( string $u = null, string $e = null, string $c = null ) : bool
	{
		self::$username 	= $u;
		self::$email 		= $e;
		self::$captcha 		= $c;

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

		$groups = getComponents( 'throttle' )->showCaptcha() 
			? $validateUserFieldWithCaptcha
			: $validateUserField;
		$rules 	= $validationComponent->getRules( $groups );
		$data 	= [
			$configValidation->user_username 	=> self::$username,
			$configValidation->user_email 		=> self::$email
		];

		$message = getBaseInstance( Message::class );

		if ( ! $validationComponent->isValid( $data, $rules ) )
		{
			$message ->errorInformation( 
				true, 
				array_values( $validationComponent->getErrors() )
			);
			return false;
		}

		$userData = model( 'User/UserModel' )->first( $data );
		if ( [] === $userData )
		{
			$message->errorInformation();
			return false;
		}

		$common 	= getComponents( 'common' );
		$randomPw 	= getBaseInstance( Password::class )->getHashPass( $common->random_string() );

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

		setSuccessMessage( $common->lang( 'Red2Horse.successResetPassword' ) );

		return true;
	}
}
