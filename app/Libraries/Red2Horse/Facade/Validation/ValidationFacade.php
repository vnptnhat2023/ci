<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Validation;

// use App\Libraries\Red2Horse\Adapter\Codeigniter\Validation\ValidationAdapterInterface;

class ValidationFacade implements ValidationFacadeInterface
{
	protected ValidationFacadeInterface $validationAdapter;

	public function __construct ( ValidationFacadeInterface $validate )
	{
		$this->validationAdapter = $validate;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate->isValid( $data, $rules );
	}

	public function getErrors ( string $field = null ) : array
	{
		return $this->validate->getErrors( $field );
	}
}