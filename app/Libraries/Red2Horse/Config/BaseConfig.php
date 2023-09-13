<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\TraitSingleton;

class BaseConfig
{
	use TraitSingleton;

	/*
	|--------------------------------------------------------------------------
	| General
	|--------------------------------------------------------------------------
	*/
	public bool $useRememberMe = true;
	public bool $useMultiLogin = false;

	/*
	|--------------------------------------------------------------------------
	| Adapter
	|--------------------------------------------------------------------------
	*/
	private const ADAPTER = 'CodeIgniter';

	public function adapter( string $name = 'Auth', ?string $diff = null ) : string
	{
		$diff = $diff ?? $name;
		return R2H_ADAPTER_NAMESPACE . self::ADAPTER . "\\{$name}\\{$diff}Adapter";
	}

	public function facade( string $name = 'Auth', ?string $diff = null ) : string
	{
		$diff = $diff ?? $name;
		return R2H_FACADE_NAMESPACE . "{$name}\\{$diff}Facade";
	}

	public function __construct ()
	{
	}
}