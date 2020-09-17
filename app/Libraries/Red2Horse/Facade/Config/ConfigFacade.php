<?php

declare( strict_types = 1 );
namespace App\Libraries\Red2Horse\Facade\Config;

# -------------------------------------------------------------------------

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

// use App\Libraries\Red2Horse\Facade\Auth\{
// 	AuthComponentBuilder,
// 	Config
// };

# -------------------------------------------------------------------------

class ConfigFacade implements ConfigFacadeInterface
{

	use TraitSingleton;

	protected ConfigFacadeInterface $config;

	# ---  Map of User Permission: [ group => route => permission ]

	# --- a: all, n: null
	protected array $userRouteGates = [ 'null', 'all' ];

	# --- a: all, n: null, c: create, r: read, u: update, d: delete
	protected array $userPermission = [ 'a', 'c', 'r', 'u', 'd' ];

	# --- Todo: 'post' => ['create'] => [ 'file', 'text', ... ]
	# --- Todo: 'post' => ['delete'] => [ 'text' ]
	# --- Todo: 'post' => ['delete'] => [ 'all' ]
	# --- Todo: 'post' => ['delete'] => [ 'null' ]

	// protected array $userPermissionCreate = [];
	// protected array $userPermissionRead = [];
	// protected array $userPermissionUpdate = [];
	// protected array $userPermissionDelete = [];

	# -------------------------------------------------------------------------

	public function __construct ( ConfigFacadeInterface $config )
	{
		$this->config = $config;
	}

	# -------------------------------------------------------------------------

	public function userPermission () : array
	{
		$data = $this->config->userPermission();

		if ( ! empty( $data ) )
		{
			$data = array_diff( array_values( $data ), $this->userPermission );
			$this->userPermission = array_merge( $this->userPermission, $data );
		}

		return $this->userPermission;
	}

	public function userRouteGates() : array
	{
		$data = $this->config->userRouteGates();

		if ( ! empty( $data ) )
		{
			$data = array_diff( array_values( $data ), $this->userRouteGates );
			$this->userRouteGates = array_merge( $this->userRouteGates, $data );
		}

		return $this->userRouteGates;
	}

	public function sessionCookieName ( ?string $name = null ) : string
	{
		return $this->config->sessionCookieName( $name );
	}

	public function sessionSavePath ( ?string $path = null ) : string
	{
		return $this->config->sessionSavePath( $path );
	}

	public function sessionExpiration ( int $expiration = 0 ) : int
	{
		return $this->config->sessionExpiration( $expiration );
	}

	public function sessionTimeToUpdate ( int $ttl = 0 ) : int
	{
		return $this->config->sessionTimeToUpdate( $ttl );
	}
}