<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Validation;

interface ValidateFacadeInterface
{
	public function isValid ( array $data, array $rules) : bool;

	public function getErrors ( string $field = null ) : array;
}