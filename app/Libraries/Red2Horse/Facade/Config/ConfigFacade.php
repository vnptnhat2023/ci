<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Config;

class ConfigFacade implements ConfigFacadeInterface
{
	protected ConfigFacadeInterface $config;

	public function __construct( ConfigFacadeInterface $config )
	{
		$this->config = $config;
	}

	public function sessionCookieName( ?string $name = null ) : string
	{
		return $this->config->sessionCookieName( $name );
	}

	public function sessionSavePath( ?string $path = null ) : string
	{
		return $this->config->sessionSavePath( $path );
	}

	public function sessionExpiration( int $expiration = 0 ) : int
	{
		return $this->config->sessionExpiration( $expiration );
	}

	public function sessionTimeToUpdate( int $ttl = 0 ) : int
	{
		return $this->config->sessionTimeToUpdate( $ttl );
	}
}