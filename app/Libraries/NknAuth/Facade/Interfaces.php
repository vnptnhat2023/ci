<?php

declare( strict_types = 1 );

namespace App\Libraries\NknAuth\Facade;

interface ValidateInterface
{
	public function isValid ( array $data);
}

interface AuthInterface
{
	public function login ( $username, $password );
}

interface UserInterface
{
	public function create ( array $data );
}

interface MailInterface
{
	public function to ( $to );
	public function subject ( $subject );
	public function send ();
}