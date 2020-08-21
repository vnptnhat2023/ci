<?php

namespace App\Libraries\NknAuth\Validation;

interface ValidationInterface
{
	public function isValid ( array $data, array $rules ) : bool;

	public function getErrors( string $field = null ) : array;
}