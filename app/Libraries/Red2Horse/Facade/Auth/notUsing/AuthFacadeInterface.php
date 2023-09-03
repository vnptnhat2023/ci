<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
interface AuthFacadeInterface
{
	/**
	 * @param string $eventName
	 * @param callable $callback
	 * @param int $priority
	 * @return mixed
	 */
	public static function eventOn ( string $eventName, callable $callback, int $priority ) : void;

	/**
	 * @param string $name
	 * @param mixed $name
	 * @return mixed $name
	 */
	public static function eventTrigger ( string $name, $args );

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool;

	/** @read_more login */
	public function logout () : bool;

	/** @read_more login */
	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool;

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

	public function isLoggedIn ( bool $withCookie = false ) : bool;

	public function regenerateCookie () : void;

	public function regenerateSession ( array $userData ) : bool;

	public function withRole ( array $role ) : bool;
	public function withPermission ( array $data, bool $or = true ) : bool;
}