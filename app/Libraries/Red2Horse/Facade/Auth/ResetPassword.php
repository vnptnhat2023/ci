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

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ResetPassword
{
	use TraitSingleton;

	private static ?string $username;
	private static ?string $email;
	private static ?string $captcha;

	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool
	{
		return getInstance( Utility::class ) ->typeChecker( 'forget', $u, null, $e, $c );
	}

	public function alreadyLoggedIn ( array $userData )
	{
		$message = getInstance( Message::class );
		$message::$successfully = true;
		$message::$success[] = getComponents( 'common' ) ->lang(
			'Red2Horse.successLoggedWithUsername',
			[ $userData[ 'username' ] ]
		);
	}

	/**
	 * Not using...
	 */
	public function resetPasswordSuccess ( array $userData ) : void
	{
		if ( ! $userEmail = explode( '@', $userData[ 'email' ] ) )
		{
			getComponents( 'common' )->log_message( 'error', "{$userEmail[ 0 ]} email INVALID !" );
			throw new \ErrorException( 'Invalid Email', 403 );
		}

		$email = str_repeat( '*', strlen( $userEmail[ 0 ] ) ) . '@' . $userEmail[ 1 ];
		$message = getInstance( Message::class );
		$message::$successfully = true;
		$message::$success[] = getComponents( 'common' )
			->lang(
				'Red2Horse.successResetPassword',
				[ $userData[ 'username' ], $email ]
			);
	}

	public function forgetHandler ( string $u = null, string $e = null, string $c = null ) : bool
	{
		self::$username = $u; self::$email = $e; self::$captcha = $c;

		$configValidation = getConfig( 'validation' );
		$validation = getComponents( 'validation' );

		$groups = getComponents( 'throttle' )->showCaptcha() 
			? [ $configValidation::$username, $configValidation::$email, $configValidation::$captcha ]
			: [ $configValidation::$username, $configValidation::$email ];

		$rules = $validation->getRules( $groups );

		$data = [
			$configValidation::$username => self::$username,
			$configValidation::$email => self::$email
		];

		$message = getInstance( Message::class );

		if ( ! $validation->isValid( $data, $rules ) )
		{
			$message ->incorrectInfo( true, array_values( $validation->getErrors() ) );
			return false;
		}

		$find_user = getComponents( 'user' )
			->getUserWithGroup( \Red2Horse\Mixins\Functions\sqlGetColumns(), $data );

		if ( empty( $find_user ) )
		{
			$message->incorrectInfo();
			return false;
		}

		$common = getComponents( 'common' );
		$randomPw = $common->random_string();
		$hashPw = getInstance( Password::class )->getHashPass( $randomPw );

		$updatePassword = getComponents( 'user' )->updateUser(
			[ 'username' => $find_user[ 'username' ] ],
			[ 'password' => $hashPw ]
		);

		$error = 'The system is busy, please come back later';

		if ( ! $updatePassword )
		{
			$message::$errors[] = $error;
			return false;
		}

		if ( ! getInstance( Notification::class ) ->mailSender( $randomPw ) )
		{
			$message::$errors[] = $error;
			$common->log_message( 'error' , "Cannot sent email: {$find_user[ 'username' ]}" );
			return false;
		}

		$message::$successfully = true;
		$message::$success[] = $common->lang( 'Red2Horse.successResetPassword' );

		return true;
	}
}
