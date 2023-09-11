<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\{
	getComponents,
	getInstance
};

class Message
{
	use TraitSingleton;

	public static bool $incorrectResetPassword = false;
	public static bool $incorrectLoggedIn = false;
	public static bool $successfully = false;
	public static bool $hasBanned = false;
	public static bool $accountInactive = false;

	public static array $errors = [];
	public static array $success = [];

	/** @return array */
	public function getResult () : array
	{
		$throttle = getComponents( 'throttle' );
		$limited = $throttle->limited();
		$attempts = $throttle->getAttempts();
		$captcha = $throttle->showCaptcha();

		/** Auth status */
		// $reset = self::$incorrectResetPassword;
		// $login = self::$incorrectLoggedIn;

		// /** Account status */
		// $suspend = self::$hasBanned;
		// $active = ! self::$accountInactive;

		/** @var bool $success */
		$success = self::$successfully;
		

		$resultMessage = [
			// 'auth_status' => [
			// 	'reset' => $reset,
			// 	'login' => $login,
			// ],

			// 'account_status' => [
			// 	'suspend' => $suspend,
			// 	'active' => ! $active
			// ],
			
			'throttle_status' => [
				'limited' => $limited,
				'attempts' => $attempts,
			],

			'show' => [
				'form' => ! $limited && ! $success,
				'captcha' => $captcha,
			]
		];

		return $resultMessage;
	}

	/**
	 * @return mixed array|object
	 */
	public function getMessage ( array $add = [], bool $asObject = true, bool $getConfig = false )
	{
		$message = [
			'message' => [
				'success' => self::$success,
				'errors' => self::$errors,
			],
			
			'result' => $this->getResult()
		];

		if ( $getConfig )
		{
			$message[ 'config' ] = get_object_vars( getInstance( Config::class ) );
		}

		if ( ! empty( $add ) )
		{
			$add = [ 'added' => $add ];
			$message = array_merge( $message, $add );
		}

		return ( $asObject ) ? json_decode( json_encode( $message ) ) : $message;
	}

	/** @return mixed array|object|void */
	public function denyMultiLogin ( bool $throttle = true, array $add = [], $getReturn = true )
	{
		! $throttle ?: getComponents( 'throttle' )->throttle();
		self::$incorrectLoggedIn = true;

		$errors[] = getComponents( 'common' )->lang( 'Red2Horse.noteLoggedInAnotherPlatform' );
		self::$errors = [ ...$errors, ...array_values( $add ) ];

		if ( $getReturn )
		{
			return $this->getMessage();
		}
	}

	/** @return mixed array|object */
	public function incorrectInfo ( bool $throttle = true, array $add = [] )
	{
		! $throttle ?: getComponents( 'throttle' )->throttle();
		self::$incorrectLoggedIn = true;

		$errors[] = getComponents( 'common' )->lang( 'Red2Horse.errorIncorrectInformation' );
		self::$errors = [ ...$errors, ...array_values( $add ) ];

		return $this->getMessage();
	}

	/**
	 * @return mixed array|object|void
	 */
	public function denyStatus ( string $status, bool $throttle = true, $getReturn = true )
	{
		! $throttle ?: getComponents( 'throttle' )->throttle();
		self::$hasBanned = ( $status === 'banned' );
		self::$accountInactive = ( $status === 'inactive' );
		self::$errors[] = getComponents( 'common' )
			->lang( 'Red2Horse.errorNotReadyYet', [ $status ] );

		if ( $getReturn )
		{
			return $this->getMessage();
		}
	}
}