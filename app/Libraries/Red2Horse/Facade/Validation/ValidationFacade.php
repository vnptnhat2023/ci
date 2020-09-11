<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Validation;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class ValidationFacade implements ValidationFacadeInterface
{
	use TraitSingleton;

	protected ValidationFacadeInterface $validate;

	public function __construct ( ValidationFacadeInterface $validate )
	{
		$this->validate = $validate;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate->isValid( $data, $rules );
	}

	public function getErrors ( string $field = null ) : array
	{
		return $this->validate->getErrors( $field );
	}

	public function getRules( $needed )
	{
		return $this->validate->getRules( $needed );
	}

	public function reset() : void
	{
		$this->validate->reset();
	}
}