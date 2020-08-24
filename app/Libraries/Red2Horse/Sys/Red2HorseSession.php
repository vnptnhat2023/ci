<?php

namespace App\Libraries\Red2Horse\Sys;

use CodeIgniter\Events\Events;
use CodeIgniter\Session\Session;

class Red2HorseSession extends Session
{
	public function __construct ( \SessionHandlerInterface $driver, $config )
	{
		parent::__construct( $driver, $config );
	}

	public function regenerate ( bool $destroy = false )
	{
		$_SESSION['__ci_last_regenerate'] = time();
		session_regenerate_id( $destroy );

		$auth = \Config\Services::Red2HorseAuth();

		if ( $auth->isLoggedIn() ) {
			Events::trigger( 'Red2HorseAuthRegenerate', $auth->getUserdata() );
		}
	}
}