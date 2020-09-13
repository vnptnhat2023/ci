<?php

declare( strict_types = 1 );

namespace App\Libraries\DesignPattern\Facade;

interface ValidateInterface
{
	public function isValid ( array $data, array $rules) : bool;

	public function getErrors ( string $field = null ) : array;
}

interface AuthInterface
{
	public function login ( string $username = null, string $password ) : bool;
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