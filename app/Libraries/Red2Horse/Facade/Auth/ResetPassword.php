<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;

use function Red2Horse\Mixins\Functions\{
	getComponents,
	getInstance
};

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

	public function forgetHandler ( string $u = null, string $e = null, string $c = null ) : bool
	{
		self::$username = $u; self::$email = $e; self::$captcha = $c;

		$config = getInstance( Config::class );
		$validation = getComponents( 'validation' );

		$group = getComponents( 'throttle' )->showCaptcha() 
			? $config::FORGET_WITH_CAPTCHA 
			: $config::FORGET;

		$rules = $validation->getRules( $config->ruleGroup[ $group ] );

		$data = [
			$config::USERNAME => self::$username,
			$config::EMAIL => self::$email
		];

		$message = getInstance( Message::class );

		if ( ! $validation->isValid( $data, $rules ) )
		{
			$message ->incorrectInfo( true, array_values( $validation->getErrors() ) );
			return false;
		}

		$find_user = getComponents( 'user' )
			->getUserWithGroup( getInstance( Config::class )->getColumString(), $data );

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
