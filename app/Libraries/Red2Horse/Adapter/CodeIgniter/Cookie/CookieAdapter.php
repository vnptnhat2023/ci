<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\Codeigniter\Cookie;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CookieAdapter implements CookieAdapterInterface
{
	use TraitSingleton;
	public function __construct ()
	{
		helper( 'cookie' );
	}

	public function get_cookie ( $index, bool $xssClean = false )
	{
		return get_cookie( $index, $xssClean );
	}

	public function set_cookie (
		$name, string $value = '', string $expire = '', string $domain = '',
		string $path = '/', string $prefix = '', bool $secure = false, bool $httpOnly = false
	): void
	{
		// dd( func_get_args() );
		if ( ! \setcookie( $name, $value, ( int ) $expire, '/', 'ci.ci' ) )
		{
			throw new \Error( 'Cannot set cookie', 403);
		}
	}

	public function delete_cookie ( $name, string $domain = '', string $path = '/', string $prefix = '' ): void
	{
		delete_cookie ( $name, $domain, $path, $prefix );
	}
}