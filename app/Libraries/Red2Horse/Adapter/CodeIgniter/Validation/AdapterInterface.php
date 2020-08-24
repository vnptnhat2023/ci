<?php

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Validation;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
interface AdapterInterface
{
	public function isValid ( array $data, array $rules ) : bool;

	public function getErrors( string $field = null ) : array;
}