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

	/**
	 * Depend on static property $returnType
	 * @return array|object
	 */
	public function getResult ()
	{
		$throttle = getComponents( 'throttle' );

		return [
			'incorrectResetPassword' => self::$incorrectResetPassword,
			'incorrectLoggedIn' => self::$incorrectLoggedIn,
			'successfully' => self::$successfully,
			'hasBanned' => self::$hasBanned,
			'accountInactive' => self::$accountInactive,
			'attempt' => $throttle->getAttempts(),
			'showCaptcha' => $throttle->showCaptcha(),
			'limited' => $throttle->limited()
		];
	}

	/**
	 * @return array|object
	 */
	public function getMessage ( array $addMore = [], bool $asObject = true )
	{
		$message = [
			'success' => self::$success,
			'errors' => self::$errors,
			'result' => $this->getResult(),
			'config' => get_object_vars( getInstance( Config::class ) )
		];

		if ( ! empty( $addMore ) )
		{
			$message = array_merge( $message, $addMore );
		}

		return ( $asObject ) ? json_decode( json_encode( $message ) ) : $message;
	}

	/** @read_more getMessage */
	public function denyMultiLogin ( bool $throttle = true, array $addMore = [], $getReturn = true )
	{
		! $throttle ?: getComponents( 'throttle' )->throttle();
		self::$incorrectLoggedIn = true;

		$errors[] = getComponents( 'common' )->lang( 'Red2Horse.noteLoggedInAnotherPlatform' );
		self::$errors = [ ...$errors, ...array_values( $addMore ) ];

		if ( $getReturn )
		{
			return $this->getMessage();
		}
	}

	/** @read_more getMessage */
	public function incorrectInfo ( bool $throttle = true, array $addMore = [] )
	{
		! $throttle ?: getComponents( 'throttle' )->throttle();
		self::$incorrectLoggedIn = true;

		$errors[] = getComponents( 'common' )->lang( 'Red2Horse.errorIncorrectInformation' );
		self::$errors = [ ...$errors, ...array_values( $addMore ) ];

		return $this->getMessage();
	}

	/**
	 * @return array|object|void
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