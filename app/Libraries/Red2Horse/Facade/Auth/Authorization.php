<?php

# --------------------------------------------------------------------------

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

# --------------------------------------------------------------------------

class Authorization
{

	use TraitSingleton;

	protected Config $config;

	/**
	 * Current session user role
	 */
	protected array $userRole;

	/**
	 * Current session user permission
	 */
	protected array $userPermission;

	# ------------------------------------------------------------------------

	public function __construct ( Config $config )
	{
		$this->config = $config;
		$this->userRole = $this->getSessionData( 'role' );
		$this->userPermission = $this->getSessionData();
	}

	# ------------------------------------------------------------------------

	# --- Todo: $or not working, cause db-role is a single column
	public function withRole ( array $needle, $or = true ) : bool
	{
		if ( $this->isInvalid() ) {
			return false;
		}

		if ( $this->isAdmin() ) {
			return true;
		}

		if ( ! $or ) {
			return $needle === $this->userRole;
		}

		$isValid = false;

		foreach ( $needle as $value )
		{
			// if ( is_string( $value ) && $value[0] === '!' ) {
			// 	$value = str_replace( [ '!', 'not' ], '', $value );

			// 	if ( in_array( $value, $this->userRole, true ) )
			// 	{
			// 		$isValid = false;
			// 		break;
			// 	}
			// }

			if ( in_array( $value, $this->userRole, true ) ) {
				$isValid = true;

				break;
			}
		}

		return $isValid;
	}

	# ------------------------------------------------------------------------

	/**
	 * The first check the current user session, * the next will be $data parameter
	 * @param array $data case empty array ( [] ) = 1st group = administrator
	 * @param bool $or when $or is false, will be check === permission, true check in_array
	 * @return boolean
	 */
	public function withPermission ( array $filters, bool $or = true ) : bool
	{
		if ( $this->isInvalid() ) {
			return false;
		}

		if ( $this->isAdmin() ) {
			return true;
		}

		if ( empty( $filters ) ) {
			return false;
		}

		$routeGates = $this->config->userRouteGates;

		$inCfPerm = fn( $filter ) : bool => in_array(
			$filter, $routeGates, true
		);

		$inUserPerm = fn( $filter ) : bool => in_array(
			$filter, $this->userPermission, true
		);

		$boolVar = true !== $or;

		if ( $or )
		{
			foreach ( $filters as $filter )
			{
				if ( true === $inCfPerm( $filter ) && true === $inUserPerm( $filter ) ) {
					$boolVar = true;
					break;
				}
			}
		}
		else
		{
			foreach ( $filters as $filter )
			{
				if ( false === $inCfPerm( $filter ) || false === $inUserPerm( $filter ) ) {
					$boolVar = false;
					break;
				}
			}
		}

		return $boolVar;
	}

	# ------------------------------------------------------------------------

	/**
	 * Use it late, because current CI4 not support filter on RestAPI method
	 * @example Gate.Permission: extension.r,extension.c
	 */
	public function withGroup ( array $dataFilters ) : bool
	{
		if ( $this->isInvalid() ) {
			return false;
		}

		if ( $this->isAdmin() ) {
			return true;
		}

		$boolVar = true;

		foreach ( $dataFilters as $gate => $filterPem )
		{
			if ( ! is_array( $filterPem ) || empty( $filterPem ) ) {
				$errStr = 'The current gate of user-permission cannot be empty !';
				throw new \Exception( $errStr, 403 );
			}

			$checkGate = $this->isValidPerm(
				(string) $gate,
				$filterPem,
				$this->userPermission
			);

			if ( false === $checkGate ) {
				echo '<pre>';
				die(var_dump([
					'gate' => $gate,
					'filterPem' => $filterPem,
					'sessionPem' => $this->userPermission
				]));
				$boolVar = false;
				break;
			}

			/**
			 * Session store pattern
			 */
			$session = [
				# --- Or 'all'
				'permission' => [
					'page' => [
						'r', 'c', 'u'
					],
					'user' => [
						'c', 'd'
					]
				]
			];
		}

		return $boolVar;
	}


	# ------------------------------------------------------------------------

	private function isValidPerm (
		string $gate,
		array $filterPem,
		array $sessionPem
	) : bool
	{
		$config = $this->config;

		$hasConfig = in_array( $gate, $config->userRouteGates, true );
		$hasSession = array_key_exists( $gate, $sessionPem );
		if ( false === $hasConfig || false === $hasSession ) {
			return false;
		}

		$currentSessionGate = $sessionPem[ $gate ] ?? [];
		if ( in_array( $config->superAdminPermission, $currentSessionGate, true ) ) {
			return true;
		}

		$hasFilterDiff = array_diff( $filterPem, $config->userPermission );
		$hasSessionDiff = array_diff( $currentSessionGate, $config->userPermission );

		if ( empty( $hasFilterDiff ) && empty( $hasSessionDiff ) ) {
			return empty( array_diff( $filterPem, $currentSessionGate ) );
		}

		return false;
	}

	private function getSessionData ( $key = 'permission' ) : array
	{
		$sessionData = ( array ) Authentication::getInstance( $this->config )
		->getUserdata( $key );

		return empty( $sessionData ) ? [] : $sessionData;
	}

	private function isAdmin () : bool
	{
		$isAdmin = ( $this->userRole === $this->config->adminRole ) ||
		isset( $this->userPermission[ 0 ] ) &&
		( $this->userPermission[ 0 ] === $this->config->adminGate );

		return ( bool ) $isAdmin;
	}

	private function isInvalid() : bool
	{
		return empty( $this->userRole[ 0] ) || empty( $this->userPermission[ 0] );
	}
}