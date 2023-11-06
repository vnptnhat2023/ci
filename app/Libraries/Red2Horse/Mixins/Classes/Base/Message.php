<?php

declare( strict_types = 1 );

namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Event\eventReturnedData;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetAttempts;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetTypes;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsLimited;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsSupported;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Message
{
	use TraitSingleton;

	private 		 		bool 		$successfully 				= false;
	public static 			bool 		$hasBanned 					= false;
	public static 			bool 		$accountInactive 			= false;
	private 		 		array 		$errors 					= [];
	private 		 		array 		$success					= [];
	private 		 		array 		$info 						= [];

	private function __construct ()
	{
		getConfig( 'event' )->init('message_show_captcha_condition', null, false );
	}

	public function setSuccessfully ( bool $successfully ) : void
	{
		$this->successfully = $successfully;
	}

	public function getSuccessfully () : bool
	{
		return $this->successfully;
	}

	/** @param array|string|\stdClass $data */
	public function setErrors ( $data ) : void
	{
		if ( ! is_array( $data ) )
		{
			$data = ( array ) $data;
		}

		$this->errors = array_merge( $this->errors, $data );
	}

	/** @param array|string|\stdClass $data */
	public function setSuccess ( $data ) : void
	{
		if ( ! is_array( $data ) )
		{
			$data = ( array ) $data;
		}

		$this->success = array_merge( $this->success, $data );
	}

	/** @param array|string|\stdClass $data */
	public function setInfo ( $data ) : void
	{
		if ( ! is_array( $data ) )
		{
			$data = ( array ) $data;
		}

		$this->info = array_merge( $this->info, $data );
	}

	public function getErrors ()
	{
		return $this->errors;
	}
	
	public function getSuccess ()
	{
		return $this->success;
	}

	public function getInfo ()
	{
		return $this->info;
	}

	private function _resultStatus () : array
	{
		$data = [
			'suspend' 		=> self::$hasBanned,
			'active' 		=> ! self::$accountInactive
		];

		return $data;
	}

	private function _resultShow () : array
	{
		helpers( [ 'throttle' ] );

		$data = [
			'form' 			=> ! throttleIsLimited() && ! $this->successfully,
			'remember_me' 	=> getConfig( 'BaseConfig' )->useRememberMe,
			'captcha' 		=> false,
			'attempts' 		=> throttleGetAttempts(),
			'attempts_type' => throttleGetTypes()
		];

		return $data;
	}

	private function _resultValidation  () : array
	{
		$configValidation 	= getConfig( 'Validation' );

		$data = [
			$configValidation->user_username,
			$configValidation->user_email,
			$configValidation->user_password,
			$configValidation->user_captcha
		];

		return $data;
	}
	
	private function _resultThrottle ( array &$data ) : array
	{
		helpers( [ 'event', 'throttle' ] );

		if ( throttleIsSupported() )
		{
			[ 'message_show_captcha_condition' => $showCaptcha ] = eventReturnedData( 
				'message_show_captcha_condition', 
				throttleGetAttempts(),
				throttleGetTypes() 
			);
			
			$data[ 'throttle_status' ] 	= [ 'limited' => throttleIsLimited() ];
			$data[ 'show' ][ 'captcha' ] 	= ( bool ) $showCaptcha;
		}

		return $data;
	}
	

	/** @return array */
	public function getResult ( ?array $add = null ) : array
	{
		$data = [
			'account_status' 	=> $this->_resultStatus(),
			'show' 				=> $this->_resultShow(),
			'validation' 		=> $this->_resultValidation()
		];

		// Reference: "$data"
		$this->_resultThrottle( $data );

		if ( null !== $add || [] !== $add )
		{
			$add	= [ 'added' => $add ];
			$data 	= array_merge( $data, $add );
		}

		return $data;
	}

	/**
	 * @return mixed array|object
	 */
	public function getMessage ( ?array $add = null, bool $asObject = true, bool $getConfig = false )
	{
		$message = [
			'message' 	=> [
				'success' 	=> $this->success,
				'errors' 	=> $this->errors,
				'normal' 	=> $this->info
			],
			'result' 	=> $this->getResult()
		];

		if ( $getConfig )
		{
			$message[ 'config' ] = get_object_vars( getConfig( 'BaseConfig' ) );
		}

		if ( null !== $add || ! empty( $add ) )
		{
			$add 		= [ 'added' => $add ];
			$message 	= array_merge( $message, $add );
		}

		return ( $asObject ) ? json_decode( json_encode( $message ) ) : $message;
	}
}