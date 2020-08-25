<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Validation;

class ValidationFacade implements ValidateFacadeInterface
{
	protected ValidateFacadeInterface $validationAdapter;

	public function __construct ( ValidateFacadeInterface $validate )
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