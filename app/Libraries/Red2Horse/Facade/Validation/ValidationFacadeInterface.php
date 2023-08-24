<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Validation;

interface ValidationFacadeInterface
{
	public function isValid ( array $data, array $rules) : bool;

	public function getErrors ( string $field = null ) : array;

	/**
	 * @param string|array|null $needed
	 * @string single rule
	 * @array multiple rules, array NOT associative
	 * @null all rules
	 * @return string|array
	 */
	public function getRules ( $needed );

	/**
	 * Clear all rules and data
	 */
	public function reset () : void;
}