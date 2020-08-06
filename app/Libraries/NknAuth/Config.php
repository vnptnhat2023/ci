<?php

namespace App\Libraries\NknAuth;

class NknAuthConfig
{
	private string $sessionName = 'oknkn';

	private string $cookieName = 'konkn';

  public const throttle = [
  	'type' => 1,
  	'limit_one' => 4,
  	'limit' => 10,
  	'timeout' => 30
	];
}