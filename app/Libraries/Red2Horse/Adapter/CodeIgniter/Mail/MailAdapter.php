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

	public function __construct( Email $email)
	{
		$this->email = $email;
	}

	public function to ( $to ) : Email
	{
		return $this->email->setTo( $to );
	}

	public function subject ( $subject ) : Email
	{
		return $this->email->setSubject( $subject );
	}

	public function message( $message ): Email
	{
		return $this->email->setMessage( $message );
	}

	public function send ( $autoClear = true ) : bool
	{
		return $this->email->send( $autoClear );
	}
}