<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Request;

class RequestFacade implements RequestFacadeInterface
{
	protected RequestFacadeInterface $request;

	public function __construct( RequestFacadeInterface $request )
	{
		$this->$request = $request;
	}

	public function get ( string $key )
	{
		return $this->request->get( $key );
	}

	public function set ( string $key, $value, $timeToLife = 86400 )
	{
		return $this->request->set( $key, $value, $timeToLife );
	}
}