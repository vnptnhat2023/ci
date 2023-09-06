<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class ConfigFacade implements ConfigFacadeInterface
{

	use TraitSingleton;

	protected ConfigFacadeInterface $config;

	# Map of User Permission: [ group => route => permission ]
	# @Todo: UserRouteGates must be ignore const::ADMINISTRATOR_GATE
	public const ADMINISTRATOR_GATE = 'all';
	public const ADMINISTRATOR_PERMISSION = 'a';
	public const ADMINISTRATOR_ROLE = 'administrator';

	public const MEMBER_ROLE = 'member';

	# @Todo: [ STR token => 'all' || 'all' => STR token ]
	# User add more feature: page, post, ...
	// public const SUPER_ADMINISTRATOR_TOKEN = 'random_bytes()';
	protected array $userRouteGates = [
		self::ADMINISTRATOR_GATE
	];

	# --- a: all, c: create, r: read, u: update, d: delete
	protected array $userPermission = [
		self::ADMINISTRATOR_PERMISSION,
		'c',
		'r',
		'u',
		'd'
	];

	protected array $userRole = [
		self::ADMINISTRATOR_ROLE,
		self::MEMBER_ROLE
	];

	protected array $sessionKey;


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

	/**
	 * @param string $name userPermission|userRouteGates|userRole
	 */
	private function getConfigUser ( string $prop = 'userPermission' ) : array
	{
		$data = $this->config->$prop();

		if ( ! empty( $data ) )
		{
			$data = array_diff( array_values( $data ), $this->$prop );
			$newData = array_merge( $this->$prop, $data );

			return $newData;
		}

		return $this->$prop;
	}

	public function getSessionKey() : array
	{
		$this->sessionKey = $this->config->getSessionKey();
		return $this->sessionKey;
	}

	public function userPermission () : array
	{
		$this->userPermission = $this->getConfigUser( 'userPermission' );

		return $this->userPermission;
	}

	public function userRouteGates() : array
	{
		$this->userRouteGates = $this->getConfigUser( 'userRouteGates' );

		return $this->userRouteGates;
	}

	public function userRole () : array
	{
		$this->userRole = $this->getConfigUser( 'userRole' );

		return $this->userRole;
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