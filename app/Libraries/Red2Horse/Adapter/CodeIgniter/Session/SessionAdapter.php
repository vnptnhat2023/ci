<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Session;

use Config\Services;

class SessionAdapter implements SessionAdapterInterface
{

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