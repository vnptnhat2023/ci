<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\Codeigniter\Request;

use Config\Services;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

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