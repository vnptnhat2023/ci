<?php
declare( strict_types = 1 );
namespace App\Libraries\Red2Horse\Auth;

use App\Libraries\NknAuth;
use App\Libraries\NknAuth\Facade\AuthInterface;

class Red2Horse implements AuthInterface
{
	protected NknAuth $auth;

	public function __construct ( AuthInterface $auth )
	{
		$this->auth = $auth;
	}

	public function login (
		string $username = null,
		string $password,
		string $email = null
	): bool
	{
		// return $this->auth->login();
		return false;
	}

}