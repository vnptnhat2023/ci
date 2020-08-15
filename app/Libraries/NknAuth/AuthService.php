<?php

# Will not working. Cause need declare a namespace for autoload


namespace FAPI\Config;
// namespace App\Libraries\NknAuth;

// use CodeIgniter\Config\Services as CoreServices;

class Services extends \CodeIgniter\Config\Services
{
	public static function test( $getShared = true )
	{
		if ($getShared) {
			return static::getSharedInstance(__FUNCTION__);
		}

		return __METHOD__ . ' blah blah';
	}
}