<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\Codeigniter\Cookie;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

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
		set_cookie( $name, $value, $expire );
		// if ( ! \setcookie( $name, $value, ( int ) $expire ) )
		// {
		// 	throw new \Error( 'Cannot set cookie', 403);
		// }
	}

	public function delete_cookie ( $name, string $domain = '', string $path = '/', string $prefix = '' ): void
	{
		delete_cookie ( $name, $domain, $path, $prefix );
	}
}