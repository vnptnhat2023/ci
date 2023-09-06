<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\getInstance;

class Notification
{
	use TraitSingleton;

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