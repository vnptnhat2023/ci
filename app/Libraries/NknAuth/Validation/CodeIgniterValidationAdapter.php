<?php
declare( strict_types = 1 );
namespace App\Libraries\NknAuth\Validation;

use App\Libraries\NknAuth\Adapter\ValidationInterface;
use CodeIgniter\HTTP\request;
use CodeIgniter\Validation\ValidationInterface as validate;

class CodeIgniterValidationAdapter implements ValidationInterface
{
	protected validate $validate;

	protected request $request;


	public function __construct ( validate $validate, request $request )
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