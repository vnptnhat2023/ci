<?php

namespace Red2Horse\Adapter\CodeIgniter\System;

use CodeIgniter\Events\Events;
use CodeIgniter\Session\Session;

class Red2HorseSession extends Session
{
	# --- Using in CI\App\Config\Services
	public function __construct ( \SessionHandlerInterface $driver, $config )
	{
		parent::__construct( $driver, $config );
	}

	public function regenerate ( bool $destroy = false )
	{
		$_SESSION['__ci_last_regenerate'] = time();
		session_regenerate_id( $destroy );

		$auth = \Config\Services::Red2HorseAuth();

		if ( $auth->isLogged() ) {
			Events::trigger( 'Red2HorseAuthRegenerate', $auth->getUserdata() );
		}
	}
}