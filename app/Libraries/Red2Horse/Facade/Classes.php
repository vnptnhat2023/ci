<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade;

use App\Libraries\Red2Horse\Facade\AuthInterface;

class Validate implements ValidateInterface
{
	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate->isValid( $data, $rules );
	}

	public function getErrors ( string $field = null ) : array
	{
		return $this->validate->getErrors( $field );
	}
}


class Auth implements AuthInterface
{
	public function login ( string $username = null, string $password ) : bool
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