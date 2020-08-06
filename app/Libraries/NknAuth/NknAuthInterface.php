<?php

namespace App\Libraries\NknAuth;

/**
 * @package NknAuth
 */
interface NknAuthInterface
{
	public function getConfig ();

	public function setConfig () : self;
}
