<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Authorization
{
	use TraitSingleton;

	public array $userRouteGates;
	public array $userPermission;
	public array $userRole;

	public string $adminGate;
	public string $adminPermission;
	public string $adminRole;

	public function __construct ()
	{
		$configFacade = getComponents( 'config' );

		$this->userRouteGates = $configFacade->userRouteGates();
		$this->userPermission = $configFacade->userPermission();
		$this->userRole = $configFacade->userRole();

		$this->adminGate = $configFacade::ADMINISTRATOR_GATE;
		$this->adminPermission = $configFacade::ADMINISTRATOR_PERMISSION;
		$this->adminRole = $configFacade::ADMINISTRATOR_ROLE;
	}
}