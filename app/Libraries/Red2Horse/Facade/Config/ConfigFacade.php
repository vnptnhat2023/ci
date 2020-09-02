<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Config;

// use App\Libraries\Red2Horse\Facade\Auth\{
// 	AuthComponentBuilder,
// 	Config
// };

class ConfigFacade implements ConfigFacadeInterface
{
	protected ConfigFacadeInterface $config;

	# --- Todo: 'post' => ['create'] => [ 'file', 'text', ... ]
	# --- Todo: 'post' => ['delete'] => [ 'text' ]
	# --- Todo: 'post' => ['delete'] => [ 'all' ]
	# --- Todo: 'post' => ['delete'] => [ 'null' ]
	protected array $userPermission = [ 'null', 'all' ];

	protected array $userPermissionAction = [ 'create', 'read', 'update', 'delete' ];

	protected array $userPermissionCreate = [];

	protected array $userPermissionRead = [];

	protected array $userPermissionUpdate = [];

	protected array $userPermissionDelete = [];


	public function __construct( ConfigFacadeInterface $config )
	{
		$this->config = $config;
	}

	public function addUserPermission ( array $perm = [] )
	{
		if ( ! empty( $perm ) ) {
			$this->userPermission += array_values( $perm );
		}
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