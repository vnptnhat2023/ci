<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class Authorization
{
	use TraitSingleton;

	protected Config $config;

	public function __construct( Config $config )
	{
		$this->config = $config;
	}

	/**
	 * The first check the current user session, * the next will be $data parameter
	 * @param array $data case empty array ( [] ) = 1st group = administrator
	 * @return boolean
	 */
	public function hasPermission ( array $data ) : bool
	{
		dd($data);
		# --- Get current user permission
		$userPerm = Authentication::getInstance( $this->config )
		->getUserdata( 'permission' );

		if ( ( false === $userPerm ) || empty( $userPerm ) )
		return false;

		if ( in_array( 'null', $userPerm, true ) )
		return false;

		if ( in_array( 'all', $userPerm, true ) )
		return true;

		# --- Administrator (1st) group !
		if ( empty( $data ) )
		return true;

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

	public function hasPermissionGroup ( array $dataFilters ) : bool
	{
		$userSessionPerm = $this->getSessionPem();

		if ( empty( $userSessionPerm ) ) {
			return false;
		}

		$boolVar = true;

		foreach ( $dataFilters as $gate => $filterGatePem )
		{
			if ( empty( $filterGatePem || ! is_array( $filterGatePem ) ) ) {
				throw new \Exception( 'The gate of user-permission cannot be empty !', 403 );
			}
			# Check in session permission in config->userPermission

			if ( false === $this->isValidPerm( (string) $gate, $filterGatePem, $userSessionPerm ) ) {
				$boolVar = false;

				break;
			}

			/**
			 * Session store pattern
			 */
			$session = [
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
		array $filterGatePem,
		array $userSessionPerm
	) : bool
	{
		$hasConfig = in_array( $gate, $this->config->userRouteGates, true );
		$hasSession = array_key_exists( $gate, $userSessionPerm );

		if ( false === $hasConfig || false === $hasSession ) {
			return false;
		}

		$hasDifferent = array_diff( $filterGatePem, $this->config->userPermission );

		return ! empty( $hasDifferent );
	}

	private function getSessionPem() : array
	{
		$userSsPerm = (array) Authentication::getInstance( $this->config )
		->getUserdata( 'permission' );

		if ( empty( $userSsPerm ) ) {
			return [];
		}

		if ( in_array( 'all', $userSsPerm, true ) ) {
			return $userSsPerm;
		}

		return $userSsPerm;
	}
}