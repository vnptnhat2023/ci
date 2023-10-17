<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Mail;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class MailFacade implements MailFacadeInterface
{
	use TraitSingleton;

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