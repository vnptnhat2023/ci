<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Config;

interface ConfigFacadeInterface
{
	public function sessionCookieName( ?string $name = null ) : string;

	public function sessionSavePath( ?string $path = null ) : string;

	public function sessionExpiration( int $expiration = 0 ) : int;

	public function sessionTimeToUpdate( int $ttl = 0 ) : int;
}