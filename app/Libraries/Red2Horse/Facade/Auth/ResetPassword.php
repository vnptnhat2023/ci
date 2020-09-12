<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

use App\Libraries\Red2Horse\Facade\{
	Validation\ValidationFacade as validation,
	Database\ThrottleFacade as throttleModel,
	Database\UserFacade as userModel,
	Common\CommonFacade as common,
};

class ResetPassword
{
	use TraitSingleton;

	protected Config $config;
	protected Red2HorseMessage $message;

	protected common $common;
	protected userModel $userModel;
	protected throttleModel $throttleModel;
	protected validation $validation;

	public function __construct( Config $config )
	{
		// $Method = [
			// Utility->typeChecker( 'forget' ),
			// Password->getHashPass( $this->common->random_string() ),
			// Notification->mailSender( $this->common->random_string() )
		// ];

		$builder = AuthComponentBuilder::createBuilder( $config )
		->common()
		->database_user()
		->database_throttle()
		->validation()
		->build();

		$this->common = $builder->common;
		$this->userModel = $builder->user;
		$this->throttleModel = $builder->throttle;
		$this->validation = $builder->validation;
		$this->message = Red2HorseMessage::getInstance( $config );
	}

	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
	) //: bool
	{
		/**
		 * ResetPassword-Todo
		 * Props: [ username, email, captcha ]
		 * Methods: Utility->typeChecker( 'forget' )
		 */

		// return Utility::getInstance()->typeChecker( 'forget', $username, null, $email, $captcha );
	}

	public function forgetHandler (
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool
	{
		/**
		 * ResetPassword-Todo
		 * Props: [ username, email ]
		 * Components: [ config, common, message, userModel, validation, throttleModel ]
		 * Methods: [
		 * Password->getHashPass( $this->common->random_string() )
		 * Notification->mailSender( $this->common->random_string() )
		 * ]
		 */
		$this->username = $username;
		$this->email = $email;
		$this->captcha = $captcha;

		$validation = $this->validation;

		$group = ( true === $this->throttleModel->showCaptcha() )
		? $this->config::FORGET_WITH_CAPTCHA
		: $this->config::FORGET;

		$rules = $validation->getRules( $this->config->ruleGroup[ $group ] );

		$data = [
			$this->config::USERNAME => $this->username,
			$this->config::EMAIL => $this->email
		];

		if ( false === $validation->isValid( $data, $rules ) ) {
			$this->message->incorrectInfo( true, array_values( $validation->getErrors() ) );

			return false;
		}

		$find_user = $this->userModel->getUser( $this->config->getColumString() ,$data );

		if ( empty( $find_user ) ) {
			$this->message->incorrectInfo();

			return false;
		}

		$randomPw = $this->common->random_string();
		$hashPw = Password::getInstance()->getHashPass( $randomPw );

		$updatePassword = $this->userModel->updateUser(
			[ 'username' => $find_user[ 'username' ] ],
			[ 'password' => $hashPw ]
		);

		$error = 'The system is busy, please come back later';

		if ( false === $updatePassword ) {
			$this->message->errors[] = $error;

			return false;
		}

		if ( ! Notification::getInstance()->mailSender( $randomPw ) ) {

			$this->message->errors[] = $error;

			$this->common->log_message(
				'error' ,
				"Cannot sent email: {$find_user[ 'username' ]}"
			);

			return false;
		}

		$this->message->successfully = true;
		$this->message->success[] = $this->common->lang( 'Red2Horse.successResetPassword' );

		return true;
	}
}
