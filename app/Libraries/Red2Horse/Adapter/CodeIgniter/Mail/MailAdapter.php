<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Mail;

use Config\Services;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
class MailAdapter implements MailAdapterInterface
{
	public function to ( $to ) : self
	{
		Services::email()->setTo( $to );

		return $this;
	}

	public function subject ( $subject ) : self
	{
		Services::email()->setSubject( $subject );

		return $this;
	}

	public function message( $message ) : self
	{
		Services::email()->setMessage( $message );

		return $this;
	}

	public function send ( bool $autoClear = true ) : bool
	{
		return Services::email()->send( $autoClear );
	}

	public function getErrors() : string
	{
		return Services::email()->printDebugger();
	}
}