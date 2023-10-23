<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Exception\ErrorParameterException;
use Red2Horse\Facade\Validation\ValidationFacadeInterface;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\BaseInstance;
use function Red2Horse\Mixins\Functions\Instance\getComponents;
use function Red2Horse\Mixins\Functions\Message\setErrorMessage;
use function Red2Horse\Mixins\Functions\Message\setSuccessMessage;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Sql\getTable;
use function Red2Horse\Mixins\Functions\Sql\getUserField;
use function Red2Horse\Mixins\Functions\Sql\selectExports;

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
		return BaseInstance( Utility::class ) ->typeChecker( 'forget', $u, null, $e, $c );
	}

	public function alreadyLoggedIn ( array $userData )
	{
		setSuccessMessage( ( array ) getComponents( 'common' )->lang(
			'Red2Horse.successLoggedWithUsername',
			[ $userData[ getUserField( 'username' ) ] ]
		) );
	}

	/**
	 * Not using...
	 */
	public function resetPasswordSuccess ( array $userData ) : void
	{
		if ( ! $userEmail = explode( '@', $userData[ getUserField( 'email' ) ] ) )
		{
			getComponents( 'common' )->log_message( 'error', "{$userEmail[ 0 ]} email INVALID !" );
			throw new ErrorParameterException( "{$userEmail[ 0 ]} email INVALID !" );
		}

		$email = str_repeat( '*', strlen( $userEmail[ 0 ] ) ) . '@' . $userEmail[ 1 ];

		setSuccessMessage( ( array ) getComponents( 'common' )->lang(
			'Red2Horse.successResetPassword',
			[ $userData[ getUserField( 'username' ) ], $email ]
		) );
	}

	public function forgetHandler ( string $u = null, string $e = null, string $c = null ) : bool
	{
		self::$username = $u; self::$email = $e; self::$captcha = $c;

		$configValidation = getConfig( 'validation' );
		/** @var ValidationFacadeInterface $validationComponent */
		$validationComponent = getComponents( 'validation' );

		$groups = getComponents( 'throttle' )->showCaptcha() 
			? [ $configValidation->user_username, $configValidation->user_email, $configValidation->user_captcha ]
			: [ $configValidation->user_username, $configValidation->user_email ];

		$rules = $validationComponent->getRules( $groups );

		$data = [
			$configValidation->user_username => self::$username,
			$configValidation->user_email => self::$email
		];

		$message = baseInstance( Message::class );

		if ( ! $validationComponent->isValid( $data, $rules ) )
		{
			$message ->errorInformation( true, array_values( $validationComponent->getErrors() ) );
			return false;
		}

		$userData = model( 'User/UserModel' )->fetchFirstUserData( $data );
		if ( [] === $userData )
		{
			$message->errorInformation();
			return false;
		}

		$common = getComponents( 'common' );
		$randomPw = BaseInstance( Password::class )->getHashPass( $common->random_string() );

		$editSet = [ getUserField( 'username' ) => $userData[ getUserField( 'username' ) ] ];
		$editWhere = [ getUserField( 'password' ) => $randomPw ];
		$error = 'Cannot update password';

		if ( ! model( 'User/UserModel' ) ->edit( $editSet, $editWhere ) )
		{
			setErrorMessage( $error );
			return false;
		}

		if ( ! baseInstance( Notification::class ) ->mailSender( $randomPw ) )
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
