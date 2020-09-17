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

		$userRoute = $this->config->userRoute;
		$boolVar = true;

		foreach ( $data as $route )
		{
			$inCfPerm = in_array( $route, $userRoute, true );
			$inUserPerm = in_array( $route, $userPerm, true );

			if ( false === $inCfPerm || false === $inUserPerm )
			{
				$boolVar = false;
				break;
			}
		}

		return $boolVar;
	}

	public function hasPermissionGroup ( array $data, string $permission ) : bool
	{
		$userCfPermission = $this->config->userPermission;

		if ( ! in_array( $permission, $userCfPermission, true ) ) {
			throw new \Exception( 'Permission not defined !', 403 );
		}

		$userSsPerm = $this->getSessionPem();

		if ( empty( $userSsPerm ) ) { return false; }

		# --- Administrator (1st) group !
		if ( empty( $data ) ) { return true; }

		$routeGates = $this->config->userRoute;
		$boolVar = true;

		foreach ( $data as $gate => $sessionPem )
		{
			$hasConfig = in_array( $gate, $routeGates, true );
			$hasSession = array_key_exists( $gate, $userSsPerm );
			$hasPem = in_array( $permission, $sessionPem );

			if ( false === $hasConfig || false === $hasSession || false === $hasPem ) {
				$boolVar = false;
				break;
			}

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

	private function getSessionPem() : array
	{
		$userSsPerm = Authentication::getInstance( $this->config )
		->getUserdata( 'permission' );

		if ( ( false === $userSsPerm ) || empty( $userSsPerm ) ) {
			return [];
		}

		if ( in_array( 'null', $userSsPerm, true ) ) {
			return [];
		}

		if ( in_array( 'all', $userSsPerm, true ) ) {
			return [];
		}

		return $userSsPerm;
	}
}