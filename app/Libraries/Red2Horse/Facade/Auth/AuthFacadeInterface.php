<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
interface AuthFacadeInterface
{
	public function login (
		string $username = null,
		string $password = null,
		bool $rememberMe = false,
		string $captcha = null
	) : bool;

	/** @read_more login */
	public function logout () : bool;

	/** @read_more login */
	public function requestPassword (
		string $username = null,
		string $email = null,
		string $captcha = null
	) : bool;

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null );

	public function getPasswordHash ( string $pass ) : string;

	/**
	 * @return object|array
	 */
	public function getMessage ( array $addMore = [], bool $asObject = true );

	/**
	 * @param array $data empty = ( 1st group === administrator group )
	 * @return boolean
	 */
	public function withPermission ( array $data ) : bool;

	/**
	 * Check cookie & session: when have cookie will set session
	 * @return boolean
	 */
	public function isLoggedIn ( bool $withCookie = false ) : bool;

	public function regenerateCookie () : void;

	public function regenerateSession ( array $userData ) : bool;
}