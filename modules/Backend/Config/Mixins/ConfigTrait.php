<?php

namespace BAPI\Config\Mixins;

/**
 * Searches an array through dot syntax. Supports wildcard searches, like foo.*.bar
 * @method getSetting @return mixed
 */
trait ConfigTrait
{
	public function getSetting ( string $key = null )
	{
		helper('array');

		$class = static::class;

		return empty( $key )
		? $class::setting
		: dot_array_search( $key, $class::setting );
	}
}