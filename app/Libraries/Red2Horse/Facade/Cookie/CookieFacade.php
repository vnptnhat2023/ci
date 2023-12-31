<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Cookie;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class CookieFacade implements CookieFacadeInterface
{
	use TraitSingleton;

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
	) : void
	{
		$this->cookie->set_cookie(
			$name, $value, $expire, $domain, $path, $prefix, $secure, $httpOnly
		);
	}

	public function delete_cookie(
		$name,
		string $domain = '',
		string $path = '/',
		string $prefix = ''
	) : void
	{
		$this->cookie->delete_cookie( $name, $domain, $path, $prefix );
	}
}