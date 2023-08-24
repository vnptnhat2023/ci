<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Request;

use Red2Horse\Mixins\TraitSingleton;

class RequestFacade implements RequestFacadeInterface
{
	use TraitSingleton;

	protected RequestFacadeInterface $request;

	public function __construct ( RequestFacadeInterface $request )
	{
		$this->request = $request;
	}

	public function post ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->post( $index, $filter, $flags );
	}

	public function get ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->get( $index, $filter, $flags );
	}

	public function getAndPost ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->getAndPost( $index, $filter, $flags );
	}

	public function postAndGet ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->postAndGet( $index, $filter, $flags );
	}

	public function getRawInput ()
	{
		return $this->request->getRawInput();
	}

	public function getIPAddress () : string
	{
		return $this->request->getIPAddress();
	}
}