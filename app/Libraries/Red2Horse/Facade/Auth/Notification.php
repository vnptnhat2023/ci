<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;

use Red2Horse\Facade\{
	Mail\MailFacade as mail
};

class Notification
{
	use TraitSingleton;

	protected Config $config;
	protected mail $mail;

	public function __construct( Config $config )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->mail()
		->build();

		$this->mail = $builder->mail;
	}

	public function mailSender ( string $randomPw ) : bool
	{
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