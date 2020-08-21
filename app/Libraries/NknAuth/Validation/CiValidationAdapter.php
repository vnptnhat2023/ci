<?php
declare( strict_types = 1 );
namespace App\Libraries\NknAuth\Validation;

use App\Libraries\NknAuth\Adapter\ValidationInterface as AdapterValidationInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Validation\ValidationInterface;

class CiValidationAdapter implements AdapterValidationInterface
{
	protected ValidationInterface $validation;

	protected RequestInterface $request;


	public function __construct ( ValidationInterface $validation, RequestInterface $request )
	{
		$this->validation = $validation;
		$this->request = $request;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validation
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