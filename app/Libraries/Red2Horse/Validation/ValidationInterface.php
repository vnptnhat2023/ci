<?php

namespace App\Libraries\Red2Horse\Validation;

/**
 * @package SimpleCiAuth
 * @author Red2Horse
 */
interface ValidationInterface
{
	public function isValid ( array $data, array $rules ) : bool;

	public function getErrors( string $field = null ) : array;
}