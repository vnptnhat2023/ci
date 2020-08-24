<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Validation;

use CodeIgniter\HTTP\request;
use CodeIgniter\Validation\ValidationInterface;

class Adapter implements AdapterInterface
{
	protected ValidationInterface $validate;

	protected request $request;


	public function __construct ( ValidationInterface $validate, request $request )
	{
		$this->validate = $validate;
		$this->request = $request;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate
		->withRequest( $this->request )
		->setRules( $rules )
		->run( $data );
	}

	public function getErrors( string $field = null ) : array
	{
		return ! empty( $field )
		? $this->validation->getErrors()
		: [ $this->validation->getError( $field ) ];
	}

}