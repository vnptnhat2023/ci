<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;
use function Red2Horse\Mixins\Functions\getInstance;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Notification
{
	use TraitSingleton;

	private function __construct () {}

	public function mailSender ( string $randomPw ) : bool
	{
		$mail = getInstance( 'mail' );

		$mail
		// ->setFrom ( 'localhost@example.com', 'Administrator' )
		->to ( 'exa@example.com' )
		// ->setCC ( 'another@another-example.com' )
		// ->setBCC ( 'them@their-example.com' )
		->subject ( 'Email Test' )
		->message ( 'your password has been reset to: ' . $randomPw );

		if ( ! $mail->send() )
		{
			throw new \Exception( $mail->getErrors(), 403 );
		}

		return true;
	}
}