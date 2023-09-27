<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Validation;

interface ValidationFacadeInterface
{
	public function ruleStore() : array;
	public function isValid ( array $data, array $rules) : bool;

	public function getErrors ( string $field = null ) : array;

	/**
	 * @param string|array|null $key
	 * @return string|array
	 */
	public function getRules ( $key = null );

	/**
	 * Clear all rules and data
	 */
	public function reset () : void;
}