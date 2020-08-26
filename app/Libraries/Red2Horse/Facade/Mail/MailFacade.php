<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Mail;

class MailFacade implements MailFacadeInterface
{
	public function to ( $to ) : self
	{
		return $this;
	}

	public function subject ( $subject ) : self
	{
		return $this;
	}

	public function send () : bool
	{
		return true;
	}
}