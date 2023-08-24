<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Facade\{
	Database\ThrottleFacadeInterface as throttleModel,
	Common\CommonFacadeInterface as common,
	Session\SessionFacadeInterface as session
};

use Red2Horse\Mixins\TraitSingleton;

# --------------------------------------------------------------------------
/**
	* @method getMessage
	* Receive all types of messages in this class
	* ```
	* $addMore = [
	*		'form' => [
	*			'username' => $_POST['username'],
	*			'email' => $_POST['email'],
	*			'password' => $_POST['password'],
	*			'captcha' => $_POST['captcha'],
	*			'remember_me' => $_POST['rememberMe']
	*		]
	* ];
	* ```
 */
class Message
{
	use TraitSingleton;

	# --- Result data
	public static bool $incorrectResetPassword = false;
	public static bool $incorrectLoggedIn = false;
	public static bool $successfully = false;
	public static bool $hasBanned = false;
	public static bool $accountInactive = false;

	# --- Message
	public static array $errors = [];
	public static array $success = [];

	# --- Components
	protected Config $config;
	protected common $common;
	protected throttleModel $throttleModel;
	protected session $session;

	# ------------------------------------------------------------------------

	public function __construct( Config $config )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->common()
		->database_throttle()
		->session()
		->build();

		$this->common = $builder->common;
		$this->throttleModel = $builder->throttle;
		$this->session = $builder->session;
	}

	# ------------------------------------------------------------------------

	# --- Todo: $returnType ( Need declare inside config file )
	/**
	 * Depend on static property $returnType
	 * @return array|object
	 */
	public function getResult ()
	{
		return [
			'incorrectResetPassword' => self::$incorrectResetPassword,
			'incorrectLoggedIn' => self::$incorrectLoggedIn,
			'successfully' => self::$successfully,
			'hasBanned' => self::$hasBanned,
			'accountInactive' => self::$accountInactive,
			'attempt' => $this->throttleModel->getAttempts(),
			'showCaptcha' => $this->throttleModel->showCaptcha()
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
			'config' => get_object_vars( $this->config )
		];

		if ( ! empty( $addMore ) ) {
			$message = array_merge( $message, $addMore );
		}

		return ( true === $asObject )
		? json_decode( json_encode( $message ) )
		: $message;
	}

	/** @read_more getMessage */
	public function denyMultiLogin
	(
		bool $throttle = true,
		array $addMore = [],
		$getReturn = true
	)
	{
		false === $throttle ?: $this->throttleModel->throttle();
		self::$incorrectLoggedIn = true;

		$errors[] = $this->common->lang( 'Red2Horse.noteLoggedInAnotherPlatform' );
		self::$errors = [ ...$errors, ...array_values( $addMore ) ];

		if ( true === $getReturn ) return $this->getMessage();
	}

	/** @read_more getMessage */
	public function incorrectInfo ( bool $throttle = true, array $addMore = [] )
	{
		false === $throttle ?: $this->throttleModel->throttle();
		self::$incorrectLoggedIn = true;

		$errors[] = $this->common->lang( 'Red2Horse.errorIncorrectInformation' );
		self::$errors = [ ...$errors, ...array_values( $addMore ) ];

		return $this->getMessage();
	}

	/**
	 * @return array|object|void
	 */
	public function denyStatus ( string $status, bool $throttle = true, $getReturn = true )
	{
		false === $throttle ?: $this->throttleModel->throttle();
		self::$hasBanned = $status === 'banned';
		self::$accountInactive = $status === 'inactive';
		self::$errors[] = $this->common->lang( 'Red2Horse.errorNotReadyYet', [ $status ] );

		if ( true === $getReturn ) return $this->getMessage();
	}
}