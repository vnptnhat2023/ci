<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Validation;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Validation\ValidationInterface;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
class ValidationAdapter implements ValidationAdapterInterface
{
	protected ValidationInterface $validate;

	public function __construct ( ValidationInterface $validate )
	{
		$this->validate = $validate;
	}

	public function isValid ( array $data, array $rules, IncomingRequest $request ) : bool
	{
		return $this->validate ->withRequest( $request ) ->setRules( $rules ) ->run( $data );
	}

	public function getErrors( string $field = null ) : array
	{
		return ! empty( $field )
		? $this->validation->getErrors()
		: [ $this->validation->getError( $field ) ];
	}

}