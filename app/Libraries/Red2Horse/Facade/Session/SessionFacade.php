<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Session;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class SessionFacade implements SessionFacadeInterface
{
	use TraitSingleton;

	protected SessionFacadeInterface $session;

	public function __construct ( SessionFacadeInterface $session )
	{
		$this->session = $session;
	}

	public function get ( string $key = null )
	{
		return $this->session->get( $key ) ?? null;
	}

	public function has (string $key): bool
	{
		return $this->session->has( $key );
	}

	public function destroy () : void
	{
		$this->session->destroy();
	}

	public function set ( $data, $value = null ) : void
	{
		$this->session->set( $data, $value );
	}
}