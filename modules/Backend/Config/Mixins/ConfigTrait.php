<?php

namespace BAPI\Config\Mixins;

trait ConfigTrait
{
	abstract public function getRules ( string $key = null ) : array;

	public function getSetting ( string $key = null )
	{
		helper('array');

		$class = static::class;

		return empty( $key )
		? $class::setting
		: dot_array_search( $key, $class::setting );
	}

	public function getRuleExcept (array $except)
	{
		return array_diff_key( $this->getRules(), array_flip( $except ) );
	}

	public function getRuleOnly (array $only)
	{
		return array_intersect_key( $this->getRules(), array_flip( $only ) );
	}
}