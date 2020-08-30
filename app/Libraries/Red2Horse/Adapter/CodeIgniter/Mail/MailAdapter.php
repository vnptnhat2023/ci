<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Mail;

use CodeIgniter\Email\Email;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
class MailAdapter implements MailAdapterInterface
{
	protected Email $email;

	public function __construct()
	{
		$this->email = \Config\Services::email();
	}

	public function to ( $to ) : self
	{
		$this->email->setTo( $to );

		return $this;
	}

	public function subject ( $subject ) : self
	{
		$this->email->setSubject( $subject );

		return $this;
	}

	public function message( $message ): self
	{
		$this->email->setMessage( $message );

		return $this;
	}

	public function send ( bool $autoClear = true ) : bool
	{
		return $this->email->send( $autoClear );
	}

	public function getErrors(): string
	{
		return $this->email->printDebugger();
	}
}