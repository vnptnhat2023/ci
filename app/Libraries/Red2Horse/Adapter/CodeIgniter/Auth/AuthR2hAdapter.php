<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\CodeIgniter\Auth;

use App\Libraries\Red2Horse\Red2Horse;

class AuthR2hAdapter extends Red2Horse
{
	protected Red2Horse $auth;

	public function __construct ( Red2Horse $auth )
	{
		$this->auth = $auth;
	}
}