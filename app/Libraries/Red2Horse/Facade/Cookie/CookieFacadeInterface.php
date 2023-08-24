<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Cookie;

interface CookieFacadeInterface
{
	function get_cookie($index, bool $xssClean = false);

	function delete_cookie(
    $name,
    string $domain = '',
    string $path = '/',
    string $prefix = ''
	) : void;

	function set_cookie(
    $name,
    string $value = '',
    string $expire = '',
    string $domain = '',
    string $path = '/',
    string $prefix = '',
    bool $secure = false,
    bool $httpOnly = false
	) : void;
}