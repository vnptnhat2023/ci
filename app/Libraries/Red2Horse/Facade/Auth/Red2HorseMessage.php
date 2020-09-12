<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\{
	Database\ThrottleFacadeInterface as throttleModel,
	Common\CommonFacadeInterface as common,
	Session\SessionFacadeInterface as session
};

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

# --------------------------------------------------------------------------

class Red2HorseMessage
{
	use TraitSingleton;

	# --- Result data
	public bool $incorrectResetPassword = false;
	public bool $incorrectLoggedIn = false;
	public bool $successfully = false;
	public bool $hasBanned = false;
	public bool $accountInactive = false;

	# --- Message
	public array $errors = [];
	public array $success = [];

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

	/**
	 * Depend on static property $returnType ( Need declare inside config file )
	 * @return array|object
	 */
	public function getResult ()
	{
		return [
			'incorrectResetPassword' => $this->incorrectResetPassword,
			'incorrectLoggedIn' => $this->incorrectLoggedIn,
			'successfully' => $this->successfully,
			'hasBanned' => $this->hasBanned,
			'accountInactive' => $this->accountInactive,
			'attempt' => $this->throttleModel->getAttempts(),
			'showCaptcha' => $this->throttleModel->showCaptcha()
		];
	}

	/**
	 * Receive all types of messages inside red2horseAuth
	 * @return array|object
	 */
	public function getMessage ( array $addMore = [], bool $asObject = true )
	{
		$message = [
			'success' => $this->success,
			'errors' => $this->errors,
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
	public function denyMultiLogin ( bool $throttle = true, array $addMore = [], $getReturn = true )
	{
		false === $throttle ?: $this->throttleModel->throttle();
		$this->incorrectLoggedIn = true;

		$errors[] = $this->common->lang( 'Red2Horse.noteLoggedInAnotherPlatform' );
		$this->errors = [ ...$errors, ...array_values( $addMore ) ];

		if ( true === $getReturn ) return $this->getMessage();
	}

	/** @read_more getMessage */
	public function incorrectInfo ( bool $throttle = true, array $addMore = [] )
	{
		false === $throttle ?: $this->throttleModel->throttle();
		$this->incorrectLoggedIn = true;

		$errors[] = $this->common->lang( 'Red2Horse.errorIncorrectInformation' );
		$this->errors = [ ...$errors, ...array_values( $addMore ) ];

		return $this->getMessage();
	}

	/**
	 * @return array|object|void
	 */
	public function denyStatus ( string $status, bool $throttle = true, $getReturn = true )
	{
		false === $throttle ?: $this->throttleModel->throttle();
		$this->hasBanned = $status === 'banned';
		$this->accountInactive = $status === 'inactive';
		$this->errors[] = $this->common->lang( 'Red2Horse.errorNotReadyYet', [ $status ] );

		if ( true === $getReturn ) return $this->getMessage();
	}
}