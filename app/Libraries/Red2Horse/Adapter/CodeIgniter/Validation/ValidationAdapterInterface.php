<?php

namespace Red2Horse\Adapter\Codeigniter\Validation;

use Red2Horse\Facade\Validation\ValidationFacadeInterface;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
interface ValidationAdapterInterface extends ValidationFacadeInterface
{
	public function isValid ( array $data, array $rules ) : bool;

	public function getErrors( string $field = null ) : array;
}