<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Request;

interface RequestFacadeInterface
{
	public function post ( $index = null, $filter = null, $flags = null );

	public function get ( $index = null, $filter = null, $flags = null );

	public function getAndPost ( $index = null, $filter = null, $flags = null );

	public function postAndGet ( $index = null, $filter = null, $flags = null );

	public function getRawInput ();

	public function getIPAddress() : string;
}