<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;
use Red2Horse\Mixins\TraitSingleton;

use function Red2Horse\Mixins\Functions\getInstance;

/**
 * @todo filter->not [ or, and, except]
 * admin
 * */
class Authorization
{
	use TraitSingleton;
	protected array $userRole;
	protected array $userPerm;
	private array $roleList = [];
	private array $permissionList = [];
	protected array $prefix = ['!', 'NOT'];
	private array $configPerm = [];
	private array $sessionPerm = [];

	public function __construct ()
	{
		// $this->userRole = $this->_getSessionData( $this->config->roleKey );
		$this->userRole = $this->_getSessionData( 'role' );
		// $this->userPerm = $this->_getSessionData( $this->config->permKey );
		$this->userPerm = $this->_getSessionData( 'permKey' );

		$this->configPerm = getInstance( Config::class )->userRouteGates;
		$this->sessionPerm = $this->userPerm;
	}

	/**
	 * @param array<string> $data
	 * @param string $k [ or, and, except; Default or ]
	 */
	public function run ( array $data, string $k = 'or' ) : bool
	{
		if ( ! $this->_check1( $data ) ) { return false; }
		if ( $this->_isAdmin() ) { return true; }

		switch ($k) {
			case 'except': return $this->_useExcept( $data );
			case 'and': return $this->_useAnd( $data );
			default: return $this->_useOr( $data );
		}
	}

	private function _useOr (array $data) : bool
	{
		if ( $data === $this->sessionPerm ) { return true; }
		return empty( array_diff( $data, $this->sessionPerm ) );
	}

	private function _useAnd (array $data) : bool
	{
		if ( $data === $this->sessionPerm ) { return true; }
		return false;
	}

	private function _useExcept (array $data) : bool
	{
		if ( $data === $this->sessionPerm ) { return true; }
		return empty( array_diff( $data, $this->sessionPerm ) );
	}

	private function _check1( array $data ) : bool
	{
		$passed = false;
		if ( empty( $this->userRole[ 0] ) || empty( $this->userPerm[ 0] ) ) {
			$passed = false;
		}

		if ( empty( $data ) ) {
			$passed = false;
		}

		return $passed;
	}

	private function _getSessionData ( $key = 'permission' ) : array
	{
		$sessionData = ( array ) getInstance( Authentication::class )->getUserdata( $key );
		return empty( $sessionData ) ? [] : $sessionData;
	}

	private function _isAdmin () : bool
	{
		$config = getInstance( Config::class );
		$isAdmin = ( $this->userRole === $config->adminRole ) ||
		isset( $this->userPerm[ 0 ] ) &&
		( $this->userPerm[ 0 ] === $config->adminGate );

		return ( bool ) $isAdmin;
	}
}