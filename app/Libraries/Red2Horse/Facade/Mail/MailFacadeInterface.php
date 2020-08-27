<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Mail;

interface MailFacadeInterface
{
	public function to ( $to ) : self;

	public function subject ( $subject ) : self;

	public function message ( $subject ) : self;

	public function send () : bool;

	public function getErrors() : string;
}