<?php

namespace App\Libraries\NknAuth;

use CodeIgniter\Events\Events;
use CodeIgniter\Session\Session;
use SessionHandlerInterface;

class NknAuthSession extends Session
{
	public function __construct ( SessionHandlerInterface $driver, $config )
	{
		parent::__construct( $driver, $config );
	}

	public function regenerate ( bool $destroy = false )
	{
		$_SESSION['__ci_last_regenerate'] = time();
		session_regenerate_id( $destroy );

		Events::trigger( 'Regenerate' );
	}
}