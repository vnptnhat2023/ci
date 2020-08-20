<?php

declare( strict_types = 1 );

namespace App\Libraries\NknAuth\Facade;

class Validate implements ValidateInterface
{
	public function isValid ( array $data ) : bool
	{
		return true;
	}
}

class Auth implements AuthInterface
{
	public function login ( $username, $password ) : bool
	{
		return true;
	}
}

class User implements UserInterface
{
	public function create ( array $data ) : bool
	{
		return true;
	}
}

class Mail implements MailInterface
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