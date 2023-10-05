<?php

declare( strict_types = 1 );
namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\
{
	getComponents,
    getConfig,
    baseInstance,
    getTable,
    getUserField,
    selectExports
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
		$message = baseInstance( Message::class );
		$message::$successfully = true;
		$message::$success[] = getComponents( 'common' ) ->lang(
			'Red2Horse.successLoggedWithUsername',
			[ $userData[ getUserField( 'username' ) ] ]
		);
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
		$message = baseInstance( Message::class );
		$message::$successfully = true;
		$message::$success[] = getComponents( 'common' )
			->lang(
				'Red2Horse.successResetPassword',
				[ $userData[ getUserField( 'username' ) ], $email ]
			);
	}

	public function forgetHandler ( string $u = null, string $e = null, string $c = null ) : bool
	{
		self::$username = $u; self::$email = $e; self::$captcha = $c;

		$configValidation = getConfig( 'validation' );
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

		$find_user = getComponents( 'user' ) ->getUserWithGroup(
			selectExports( [ getTable( 'user' ) => [], getTable( 'user_group' ) => [] ] ),
			$data 
		);

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
			$message::$errors[] = $error;
			return false;
		}

		if ( ! baseInstance( Notification::class ) ->mailSender( $randomPw ) )
		{
			$message::$errors[] = $error;
			$common->log_message( 'error' , "Cannot sent email: {$find_user[ getUserField( 'username' ) ]}" );
			return false;
		}

		$message::$successfully = true;
		$message::$success[] = $common->lang( 'Red2Horse.successResetPassword' );

		return true;
	}
}
