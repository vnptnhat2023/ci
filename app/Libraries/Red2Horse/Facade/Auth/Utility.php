<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

use App\Libraries\Red2Horse\Facade\{
	Common\CommonFacade as common,
	Database\ThrottleFacade as throttleModel
};

class Utility
{
	use TraitSingleton;

	protected Config $config;

	protected common $common;
	protected throttleModel $throttleModel;

	public function __construct( Config $config )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->common()
		->database_throttle()
		->build();

		$this->common = $builder->common;
		$this->throttleModel = $builder->throttle;
	}

	/**
	 * @param string $type login|forget
	 * @param string|null $username
	 * @param string|null $password
	 * @param string|null $email
	 * @param string|null $captcha
	 * @return bool
	 * @throws \Exception
	 */
	public function typeChecker (
		$type = 'login',
		string $username = null,
		string $password = null,
		string $email = null,
		string $captcha = null
	) : bool
	{
		/**
		 * Utility-Todo
		 * Props: [ username, password, email ]
		 * Components: [ config, common, message, throttleModel ]
		 * Methods: [
		 * Authentication->isLogged( true )
		 * Authentication->setLoggedInSuccess( $this->getUserdata() )
		 * Authentication->getUserdata()
		 * Authentication->loginHandler()
		 *
		 * ResetPassword->forgetHandler()
		 * ]
		 */
		if ( ! in_array( $type, [ 'login', 'forget' ] ) ) {
			throw new \Exception( 'Type must be in "login or forget"', 1 );
		}

		$requestType = ( $type === 'login' ) ? 'password' : 'email';
		$isNullUsername = is_null( $username );
		$isNullType = is_null( $$requestType );

		$hasRequest = ! $isNullUsername && ! $isNullType;

		if ( true === Authentication::getInstance( $this->config )->isLogged( true ) )
		{
			( $type === 'forget' )
			? Message::getInstance( $this->config )::$incorrectResetPassword = true
			: Authentication::getInstance( $this->config )
			->setLoggedInSuccess(
				Authentication::getInstance( $this->config )->getUserdata()
			);

			return true;
		}

		if ( true === $this->throttleModel->limited() )
		{
			$errArg = [
				'num' => gmdate( 'i', $this->config->throttle->timeoutAttempts ),
				'type' => 'minutes'
			];
			$errStr = $this->common->lang( 'Red2Horse.errorThrottleLimitedTime', $errArg );
			Message::getInstance( $this->config )::$errors[] = $errStr;

			return false;
		}

		if ( false === $hasRequest ) return false;

		return ( $type === 'login' )
		? Authentication::getInstance( $this->config )->loginHandler()
		: ResetPassword::getInstance( $this->config )->forgetHandler( $username, $email, $captcha );
	}

	public function trigger ( \Closure $closure, string $event, array $eventData )
	{
		if ( ! isset( $closure->{$event} ) || empty( $closure->{$event} ) ) return $eventData;

		foreach ( $closure->{$event} as $callback )
		{
			if ( ! method_exists( $closure, $callback ) ) {
				throw new \Exception( 'Invalid Method Triggered', 403 );
			}

			$eventData = $closure->{$callback}( $eventData );
		}

		return $eventData;
	}
}