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

	protected Authentication $authentication;

	/**
	 * Current session user role
	 */
	protected array $sessionRole;

	/**
	 * Current session user permission
	 */
	protected array $sessionPermission;

	# ------------------------------------------------------------------------

	public function __construct ( Config $config )
	{
		$this->config = $config;
		$this->authentication = Authentication::getInstance( $this->config );

		$this->sessionRole = $this->getSessionData( 'role' );
		$this->sessionPermission = $this->getSessionData( 'permission' );
	}

	# ------------------------------------------------------------------------

	/**
	 * @param array $filters
	 * @param bool $onlyRole when false will be do except
	 * @return bool
	 */
	public function withRole ( array $filters, $only = 'gdg' ) : bool
	{
		if ( $this->isInvalid() ) {
			return false;
		}

		if ( $this->isAdmin() ) {
			// dd('admin');
			return true;
		}
		// dd($only);
		$isValid = false;

		$checked = [];

		foreach ( $filters as $filter )
		{
			$filter = str_replace( ' ', '', ( string ) $filter );

			if ( in_array( $filter, $checked, true ) ) {
				continue;
			}

			// if ( $filter[ 0 ] === '!' )
			// {
			// 	# saved not, check them for all other filter ?
			// 	$filterExcept = str_replace( [ '!', 'NOT' ], '', $filter );
			// 	$checked[] = $filterExcept;

			// 	continue;
			// }

			if ( in_array( $filter, $this->sessionRole, true ) )
			{
				$checked[] = $filter;

				$isValid = ! $isValid;
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
			$filter, $this->sessionPermission, true
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
				( string ) $gate,
				$filterPem,
				$this->sessionPermission
			);

			if ( false === $checkGate ) {
				echo '<pre>';
				die(var_dump([
					'gate' => $gate,
					'filterPem' => $filterPem,
					'sessionPem' => $this->sessionPermission
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

		$hasFilterDiff = array_diff( $filterPem, $config->sessionPermission );
		$hasSessionDiff = array_diff( $currentSessionGate, $config->sessionPermission );

		if ( empty( $hasFilterDiff ) && empty( $hasSessionDiff ) ) {
			return empty( array_diff( $filterPem, $currentSessionGate ) );
		}

		return false;
	}

	private function getSessionData ( $key = 'permission' ) : array
	{
		$sessionData = ( array ) $this->authentication->getUserdata( $key );

		return empty( $sessionData ) ? [] : $sessionData;
	}

	private function isAdmin () : bool
	{
		$isAdmin = ( $this->sessionRole === $this->config->adminRole ) ||
		isset( $this->sessionPermission[ 0 ] ) &&
		( $this->sessionPermission[ 0 ] === $this->config->adminGate );

		return ( bool ) $isAdmin;
	}

	private function isInvalid() : bool
	{
		return empty( $this->sessionRole[ 0] ) || empty( $this->sessionPermission[ 0] );
	}
}