<?php

declare( strict_types = 1 );

namespace App\Libraries\NknAuth\Facade;

use App\Libraries\NknAuth\Adapter\ValidationInterface;

interface ValidateInterface
{
	public function __construct( ValidationInterface $validationAdapter );

	public function isValid ( array $data, array $rules) : bool;

	public function getErrors ( string $field = null ) : array;
}

interface AuthInterface
{
	public function login ( $username, $password ) : bool;
}

interface UserInterface
{
	public function create ( array $data ) : bool;
}

interface MailInterface
{
	public function to ( $to ) : self;

	public function subject ( $subject ) : self;

	public function send () : bool;
}