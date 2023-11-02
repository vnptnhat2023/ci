<?php

declare( strict_types = 1 );

namespace Red2Horse\Mixins\Classes\Base;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\helpers;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Event\eventReturnedData;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetAttempts;
use function Red2Horse\Mixins\Functions\Throttle\throttleGetTypes;
use function Red2Horse\Mixins\Functions\Throttle\throttleInstance;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsLimited;
use function Red2Horse\Mixins\Functions\Throttle\throttleIsSupported;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Message
{
	use TraitSingleton;

	public static 		bool 		$incorrectResetPassword 	= false;
	public static 		bool 		$incorrectLoggedIn 			= false;
	public static 		bool 		$successfully 				= false;
	public static 		bool 		$hasBanned 					= false;
	public static 		bool 		$accountInactive 			= false;
	public static 		array 		$errors 					= [];
	public static 		array 		$success					= [];
	public static 		array 		$info 						= [];

	private function __construct ()
	{
		getConfig( 'event' )->init('message_show_captcha_condition', null, false );
	}

	/** @return array */
	public function getResult ( ?array $add = null ) : array
	{
		helpers( [ 'throttle' ] );
		$baseConfig 		= getConfig( 'BaseConfig' );
		$configValidation 	= getConfig( 'Validation' );
		$suspend 			= self::$hasBanned;
		$active 			= ! self::$accountInactive;

		$resultMessage = [
			// 'auth_status' => [ 'reset' => $reset, 'login' => $login ],
			'account_status' => [
				'suspend' 		=> $suspend,
				'active' 		=> ! $active
			],
			'show' 			=> [
				'form' 			=> ! throttleIsLimited() && ! self::$successfully,
				'remember_me' 	=> $baseConfig->useRememberMe,
				'captcha' 		=> false,
				'attempts' 		=> throttleGetAttempts(),
				'attempts_type' => throttleGetTypes()
			],
			'validation' 	=> [
				$configValidation->user_username,
				$configValidation->user_email,
				$configValidation->user_password,
				$configValidation->user_captcha
			]
		];

		if ( throttleIsSupported() )
		{
			[ 'message_show_captcha_condition' => $showCaptcha ] = eventReturnedData( 
				'message_show_captcha_condition', 
				throttleGetAttempts(), throttleGetTypes() 
			);
			
			$resultMessage[ 'throttle_status' ] 	= [ 'limited' => throttleIsLimited() ];
			$resultMessage[ 'show' ][ 'captcha' ] 	= ( bool ) $showCaptcha;
		}

		if ( null !== $add || ! empty( $add ) )
		{
			$add 			= [ 'added' => $add ];
			$resultMessage 	= array_merge( $resultMessage, $add );
		}

		return $resultMessage;
	}

	/**
	 * @return mixed array|object
	 */
	public function getMessage ( ?array $add = null, bool $asObject = true, bool $getConfig = false )
	{
		$message = [
			'message' 	=> [
				'success' 	=> self::$success,
				'errors' 	=> self::$errors,
				'normal' 	=> self::$info
			],
			'result' 	=> $this->getResult()
		];

		if ( $getConfig )
		{
			$message[ 'config' ] = get_object_vars( getConfig() );
		}

		if ( null !== $add || ! empty( $add ) )
		{
			$add 		= [ 'added' => $add ];
			$message 	= array_merge( $message, $add );
		}

		return ( $asObject ) ? json_decode( json_encode( $message ) ) : $message;
	}
}