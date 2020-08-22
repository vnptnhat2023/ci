<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade;

use App\Libraries\Red2Horse\Validation\ValidationInterface;

/**
 * Validation adapter class
 *
 * ---
 *
 * ```
 * new CodeigniterValidation( config( 'validation' ), config( 'request' ) )
 * ```
 */
class Validate implements ValidateInterface
{
	protected ValidationInterface $validationAdapter;

	public function __construct ( ValidationInterface $validationAdapter )
	{
		$this->validationAdapter = $validationAdapter;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validationAdapter->isValid( $data, $rules );
	}

	public function getErrors ( string $field = null ) : array
	{
		return $this->validationAdapter->getErrors( $field );
	}
}

class Auth implements AuthInterface
{
	public function login (
		string $username = null,
		string $password,
		string $email = null
	) : bool
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