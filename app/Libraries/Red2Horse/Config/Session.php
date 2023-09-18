<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Session
{
	use TraitSingleton;

    private const SESSION_NAME = 'r2h';

	public string $session = self::SESSION_NAME;
	public string $sessionSavePath = '';
	public string $sessionCookieName = 'r2h';
	public int $sessionExpiration = 0;
	public int $sessionTimeToUpdate = 0;
	public array $sessionKey;

	public function __construct ()
	{
		$configFacade = getComponents( 'config' );
		
		$this->sessionSavePath = $configFacade->sessionSavePath();
		$this->sessionCookieName = $configFacade->sessionCookieName();
		$this->sessionExpiration = $configFacade->sessionExpiration();
		$this->sessionTimeToUpdate = $configFacade->sessionTimeToUpdate();

		$this->sessionKey = $configFacade->getSessionKey();
	}
}