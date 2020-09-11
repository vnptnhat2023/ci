<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Request;

use Config\Services;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
class RequestAdapter implements RequestAdapterInterface
{
	public function post ( $index = null, $filter = null, $flags = null )
	{
		return Services::request()->getPost( $index, $filter, $flags );
	}

	public function get ( $index = null, $filter = null, $flags = null )
	{
		return Services::request()->getGet( $index, $filter, $flags );
	}

	public function getAndPost ( $index = null, $filter = null, $flags = null )
	{
		return Services::request()->getPostGet( $index, $filter, $flags );
	}

	public function postAndGet ( $index = null, $filter = null, $flags = null )
	{
		return Services::request()->getGetPost( $index, $filter, $flags );
	}

	public function getRawInput ()
	{
		return Services::request()->getRawInput();
	}

	public function getIPAddress() : string
	{
		return Services::request()->getIPAddress();
	}
}