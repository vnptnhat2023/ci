<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Cookie;

class CookieAdapterHelper implements CookieAdapterInterface
{
	public function __construct()
	{
		helper( 'cookie' );
	}

	public function get_cookie ( $index, bool $xssClean = false )
	{
		return get_cookie( $index, $xssClean );
	}

	public function set_cookie (
		$name,
		string $value = '',
		string $expire = '',
		string $domain = '',
		string $path = '/',
		string $prefix = '',
		bool $secure = false,
		bool $httpOnly = false
	): void
	{
		set_cookie(
			$name,
			$value,
			$expire,
			$domain,
			$path,
			$prefix,
			$secure,
			$httpOnly
		);
	}

	public function delete_cookie (
		$name,
		string $domain = '',
		string $path = '/',
		string $prefix = ''
	): void
	{
		delete_cookie ( $name, $domain, $path, $prefix );
	}
}