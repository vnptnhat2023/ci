<?php

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Validation;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
interface ValidationAdapterInterface
{
	public function isValid ( array $data, array $rules, IncomingRequest $request ) : bool;

	public function getErrors( string $field = null ) : array;
}