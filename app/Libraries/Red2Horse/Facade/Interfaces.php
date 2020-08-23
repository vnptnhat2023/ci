<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade;

use App\Libraries\Red2Horse\validation\ValidationInterface;

interface ValidateInterface
{
	public function __construct( ValidationInterface $validationAdapter );

	public function isValid ( array $data, array $rules) : bool;

	public function getErrors ( string $field = null ) : array;
}

interface AuthInterface
{
	public function login (
		string $username = null,
		string $password,
		bool $rememberMe = false
	) : bool;

	public function logout ( bool $returnType = true ) : bool;

	public function requestPassword (
		string $username,
		string $email,
		bool $returnType = true
	) : bool;

	public function getUserdata ( string $key = null );

	public function getPasswordHash ( string $pass, int $cost = 12 ) : string;

	public function getMessage ( array $addMore = [] ) : array;

	public function withPermission ( array $data ) : bool;

	public function isLoggedIn ( bool $withCookie = false ) : bool;

	public function regenerateCookie () : void;
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