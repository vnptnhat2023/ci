<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\Codeigniter\Mail;

use Config\Services;
use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class MailAdapter implements MailAdapterInterface
{
	use TraitSingleton;
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