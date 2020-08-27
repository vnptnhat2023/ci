<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Session;

use CodeIgniter\Session\SessionInterface;

class SessionAdapter implements SessionAdapterInterface
{
	protected SessionInterface $session;

	public function __construct ( SessionInterface $session )
	{
		$this->session = $session;
	}

	public function get ( string $key = null )
	{
		return $this->session->get( $key );
	}

	public function has (string $key): bool
	{
		return $this->session->has( $key );
	}

	public function destroy () : void
	{
		$this->session->destroy();
	}

	public function getFlashdata ( string $key = null )
	{
		$this->session->getFlashdata( $key );
	}

	public function set ( $data, $value = null ) : void
	{
		$this->session->set( $data, $value );
	}
}