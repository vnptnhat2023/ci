<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Config;

# --------------------------------------------------------------------------

class ConfigAdapter implements ConfigAdapterInterface
{

	protected string $sessionCookieName;
	protected string $sessionSavePath;
	protected int $sessionExpiration;
	protected int $sessionTimeToUpdate;

	# -------------------------------------------------------------------------

	public function __construct()
	{
		$appConfig = config( '\Config\App' );

		$this->sessionCookieName = $appConfig->sessionCookieName;
		$this->sessionSavePath = $appConfig->sessionSavePath;
		$this->sessionExpiration = $appConfig->sessionExpiration;
		$this->sessionTimeToUpdate = $appConfig->sessionTimeToUpdate;
	}

	# -------------------------------------------------------------------------
	public function sessionCookieName( ?string $name = null ) : string
	{
		if ( null === $name ) {
			return $this->sessionCookieName;
		}

		return $this->sessionCookieName = $name;
	}

	public function sessionSavePath( ?string $path = null ) : string
	{
		if ( null === $path ) {
			return $this->sessionSavePath;
		}

		return $this->sessionSavePath = $path;
	}

	public function sessionExpiration( int $expiration = 0 ) : int
	{
		if ( $expiration <= 0 ) {
			return $this->sessionExpiration;
		}

		return $this->sessionExpiration = $expiration;
	}

	public function sessionTimeToUpdate( int $TimeToUpdate = 0 ) : int
	{
		if ( $TimeToUpdate <= 0 ) {
			return $this->sessionTimeToUpdate;
		}

		return $this->sessionTimeToUpdate = $TimeToUpdate;
	}
}