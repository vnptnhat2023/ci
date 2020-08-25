<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Request;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
interface RequestAdapterInterface
{
	public function post ( $index = null, $filter = null, $flags = null );

	public function get ( $index = null, $filter = null, $flags = null );

	public function getAndPost ( $index = null, $filter = null, $flags = null );

	public function postAndGet ( $index = null, $filter = null, $flags = null );

	public function getRawInput ();
}