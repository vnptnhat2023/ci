<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Request;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
class RequestAdapter implements RequestAdapterInterface
{
	protected IncomingRequest $request;

	public function __construct()
	{
		$this->request = \Config\Services::request();
	}

	public function post ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->getPost( ...func_get_args() );
	}

	public function get ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->getGet( ...func_get_args() );
	}

	public function getAndPost ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->getPostGet( ...func_get_args() );
	}

	public function postAndGet ( $index = null, $filter = null, $flags = null )
	{
		return $this->request->getGetPost( ...func_get_args() );
	}

	public function getRawInput ()
	{
		return $this->request->getRawInput();
	}

	public function getIPAddress() : string
	{
		return $this->request->getIPAddress();
	}
}