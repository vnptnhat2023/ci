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

	protected array $userPermission = [];
	protected array $userRouteGates = [];

	# -------------------------------------------------------------------------

	public function __construct()
	{
		$appConfig = config( '\Config\App' );

		$this->sessionCookieName = $appConfig->sessionCookieName;
		$this->sessionSavePath = $appConfig->sessionSavePath;
		$this->sessionExpiration = $appConfig->sessionExpiration;
		$this->sessionTimeToUpdate = $appConfig->sessionTimeToUpdate;

		$this->userRoute = $appConfig->userRoute;
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

	public function userPermission ( array $perm = [] ) : array
	{
		if ( ! empty( $perm ) ) {
			$this->userPermission += array_values( $perm );
		}

		return $this->userPermission;
	}

	public function userRouteGates( array $gate = [] ) : array
	{
		if ( ! empty( $gate ) ) {
			$this->userRouteGates += array_values( $gate );
		}

		return $this->userRouteGates;
	}
}