<?php

namespace App\Libraries\Red2Horse\Auth;

/**
 * @package Red2HorseAuth
 * @author Red2Horse
 */
interface AuthInterface
{

	/**
	 * @param true $returnType get result as object
	 * @param false $returnType get result as array
	 *
	 * @return array
	 */
	public function login (
		string $username = null,
		string $password,
		bool $rememberMe = false
	) : bool;

	/** @read_more login */
	public function logout ( bool $returnType = true ) : bool;

	/** @read_more login */
	public function requestPassword (
		string $username,
		string $email,
		bool $returnType = true
	) : bool;

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null );

	public function getPasswordHash ( string $pass, int $cost = 12 ) : string;

	public function getMessage ( array $addMore = [] ) : array;

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
}