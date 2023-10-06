<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\Session;

use Config\Services;
use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class SessionAdapter implements SessionAdapterInterface
{
	use TraitSingleton;

	public function get ( string $key = null )
	{
		return Services::session()->get( $key );
	}

	public function has (string $key): bool
	{
		return Services::session()->has( $key );
	}

	public function destroy () : void
	{
		Services::session()->destroy();
	}

	public function set ( $data, $value = null ) : void
	{
		Services::session()->set( $data, $value );
	}
}