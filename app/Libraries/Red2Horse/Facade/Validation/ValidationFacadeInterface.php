<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Validation;

interface ValidationFacadeInterface
{
	public function isValid ( array $data, array $rules) : bool;

	public function getErrors ( string $field = null ) : array;

	// public function getSingleRule( string $key ) : array;
	// public function getMultiRule( string $key ) : array;
	// public function getGroupRule( string $key ) : array;

	/**
	 * @param $key string|array|null
	 * @return mixed
	 */
	public function getRules ( $needed );

	public function reset () : void;
}