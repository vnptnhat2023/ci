<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Auth;

/**
 * @package Red2HorseAuth
 * @author Red2Horse
 */
interface AuthAdapterInterface
{
	public function login (
		string $username = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	) : bool;

	public function logout () : bool;

	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool;

	public function getUserdata ( string $key = null );

	public function getPasswordHash ( string $pass, int $cost = 12 ) : string;

	public function getMessage ( array $addMore = [] ) : array;

	public function withPermission ( array $data ) : bool;

	public function isLoggedIn ( bool $withCookie = false ) : bool;

	public function regenerateCookie () : void;

	public function regenerateSession ( array $userData ) : bool;
}