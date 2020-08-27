<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Mail;

class MailFacade implements MailFacadeInterface
{
	protected MailFacadeInterface $mail;

	public function __construct( MailFacadeInterface $mail )
	{
		$this->mail = $mail;
	}

	public function to ( $to ) : self
	{
		$this->mail->to( $to );

		return $this;
	}

	public function subject ( $subject ) : self
	{
		$this->mail->subject( $subject );

		return $this;
	}

	public function message ( $subject ) : self
	{
		$this->mail->message( $subject );

		return $this;
	}

	public function send () : bool
	{
		return $this->mail->send();
	}

	public function getErrors(): string
	{
		return $this->mail->getErrors();
	}
}