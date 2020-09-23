<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class Authorization
{
	use TraitSingleton;

	protected Config $config;

	public function __construct ( Config $config )
	{
		$this->config = $config;
	}

	public function withRole ( string $roleName ) : bool
	{
		if ( true === $this->specialPermission() ) {
			return true;
		}

		return in_array(
			$roleName,
			$this->getSessionData( 'role' ),
			true
		);
	}

	/**
	 * The first check the current user session, * the next will be $data parameter
	 * @param array $data case empty array ( [] ) = 1st group = administrator
	 * @return boolean
	 */
	public function withPermission ( array $data ) : bool
	{
		$userPerm = $this->getSessionData();

		if ( empty( $userPerm ) ) return false;

		if ( in_array( 'null', $userPerm, true ) ) return false;

		if ( in_array( 'all', $userPerm, true ) ) return true;

		# --- Administrator (1st) group !
		if ( empty( $data ) ) return true;

		$routeGates = $this->config->userRouteGates;
		$boolVar = true;

		foreach ( $data as $route )
		{
			$inCfPerm = in_array( $route, $routeGates, true );
			$inUserPerm = in_array( $route, $userPerm, true );

			if ( false === $inCfPerm || false === $inUserPerm )
			{
				$boolVar = false;
				break;
			}
		}

		return $boolVar;
	}

	/**
	 * Use it late, because current CI4 not support filter on RestAPI method
	 * @example Gate.Permission: extension.r,extension.c
	 */
	public function withGroup ( array $dataFilters ) : bool
	{
		$sessionPem = $this->getSessionData();

		if ( empty( $sessionPem ) ) {
			return false;
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
				$sessionPem
			);

			if ( false === $checkGate ) {
				echo '<pre>';
				die(var_dump([
					'gate' => $gate,
					'filterPem' => $filterPem,
					'sessionPem' => $sessionPem
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

	private function isValidPerm (
		string $gate,
		array $filterPem,
		array $sessionPem
	) : bool
	{
		if ( true === $this->specialPermission() ) {
			return true;
		}

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

	private function specialPermission () : bool
	{
		$sessionPem = $this->getSessionData();
		$AdminGate = $this->config->superAdminGate;

		if ( isset( $sessionPem[ 0 ] ) && $sessionPem[ 0 ] === $AdminGate ) {
			return true;
		}

		return false;
	}
}