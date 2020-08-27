<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Cookie;

class CookieFacade implements CookieFacadeInterface
{
	protected CookieFacadeInterface $cookie;

	public function __construct ( CookieFacadeInterface $cookie )
	{
		$this->cookie = $cookie;
	}

	public function get_cookie( $index, bool $xssClean = false )
	{
		return $this->cookie->get_cookie( $index, $xssClean );
	}

	public function set_cookie(
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
		$this->set_cookie(
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

	public function delete_cookie(
		$name, string
		$domain = '',
		string $path = '/',
		string $prefix = ''
	): void
	{
		$this->cookie->delete_cookie( ...func_get_args() );
	}
}