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
		$sessionPerm = $this->getSessionPem();

		if ( empty( $sessionPerm ) ) {
			return false;
		}

		$boolVar = true;

		foreach ( $dataFilters as $gate => $filterPem )
		{
			if ( ! is_array( $filterPem ) || empty( $filterPem ) ) {
				throw new \Exception( 'The gate of user-permission cannot be empty !', 403 );
			}

			$unknown = $this->isValidPerm( (string) $gate, $filterPem, $sessionPerm );
			// die(var_dump($unknown));
			if ( false === $unknown ) {
				die('here? false === $unknown');
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

		die('it true@@@@@@@@@');
		return $boolVar;
	}

	private function isValidPerm ( string $gate, array $filterPem, array $sessionPerm ) : bool
	{
		$hasConfig = in_array( $gate, $this->config->userRouteGates, true );
		$hasSession = array_key_exists( $gate, $sessionPerm );

		if ( false === $hasConfig || false === $hasSession ) {
			// d( 'hasConfig', $gate, $this->config->userRouteGates );
			// dd( 'hasSession', $gate, $sessionPerm );
			return false;
		}

		$hasFilterDiff = array_diff( $filterPem, $this->config->userPermission );
		// die(var_dump( $sessionPerm[ $gate ], $this->config->userPermission ));
		$hasSessionDiff = array_diff( $sessionPerm[ $gate ], $this->config->userPermission );
		// var_dump( $hasFilterDiff, $hasSessionDiff );
		// die(var_dump( empty( $hasFilterDiff ), empty( $hasSessionDiff ) ));
		if ( empty( $hasFilterDiff ) && empty( $hasSessionDiff ) ) {
			return true;
		}

		return false;
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