<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Facade\Validation\ValidationFacadeInterface;
use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    baseInstance,
    getTable,
    getUserField,
    selectExports,
    setErrorMessage,
    setSuccessMessage
};

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
		return baseInstance( Utility::class ) ->typeChecker( 'forget', $u, null, $e, $c );
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
			throw new \ErrorException( 'Invalid Email', 403 );
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

		$querySelectSql = selectExports( [
			getTable( 'user' ) => [],
			getTable( 'user_group' ) => []
		]);
		$find_user = getComponents( 'user' ) ->getUserWithGroup( $querySelectSql, $data );

		if ( empty( $find_user ) )
		{
			$message->errorInformation();
			return false;
		}

		$common = getComponents( 'common' );
		$randomPw = $common->random_string();
		$hashPw = baseInstance( Password::class )->getHashPass( $randomPw );

		$updatePassword = getComponents( 'user' )->updateUser(
			[ getUserField( 'username' ) => $find_user[ getUserField( 'username' ) ] ],
			[ getUserField( 'password' ) => $hashPw ]
		);

		$error = 'The system is busy, please come back later';

		if ( ! $updatePassword )
		{
			setErrorMessage( $error );
			return false;
		}

		if ( ! baseInstance( Notification::class ) ->mailSender( $randomPw ) )
		{
			setErrorMessage( $error );
			$common->log_message( 'error' , "Cannot sent email: {$find_user[ getUserField( 'username' ) ]}" );
			return false;
		}

		setSuccessMessage( $common->lang( 'Red2Horse.successResetPassword' ) );

		return true;
	}
}
