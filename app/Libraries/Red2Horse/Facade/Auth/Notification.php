<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

use App\Libraries\Red2Horse\Facade\{
	Mail\MailFacade as mail
};

class Notification
{
	use TraitSingleton;

	protected mail $mail;

	public function __construct( mail $mail )
	{
		$this->mail = $mail;
	}

	public function mailSender ( string $randomPw ) : bool
	{
		/**
		 * Notification-Todo
		 * Components: [ mail ]
		 */
		$this->mail
		// ->setFrom ( 'localhost@example.com', 'Administrator' )
		->to ( 'exa@example.com' )
		// ->setCC ( 'another@another-example.com' )
		// ->setBCC ( 'them@their-example.com' )
		->subject ( 'Email Test' )
		->message ( 'your password has been reset to: ' . $randomPw );

		if ( false === $this->mail->send() ) {
			throw new \Exception( $this->mail->getErrors(), 403 );
		}

		return true;
	}
}