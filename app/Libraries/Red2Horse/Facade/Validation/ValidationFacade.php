<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Validation;

use Red2Horse\Mixins\Traits\TraitSingleton;

use function Red2Horse\Mixins\Functions\getComponents;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ValidationFacade implements ValidationFacadeInterface
{
	use TraitSingleton;

	protected ValidationFacadeInterface $validate;

	public function __construct ( ValidationFacadeInterface $validate )
	{
		$this->validate = $validate;
	}

	public function reInit () : void
	{
		$this->validate = getComponents( 'validation', 'RegistryClass', true, true );
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate->isValid( $data, $rules );
	}

	public function getErrors ( string $field = null ) : array
	{
		return $this->validate->getErrors( $field );
	}

	/** @param null|string|array $keys */
	public function getRules( $key = null )
	{
		return $this->validate->getRules( $key );
	}

	public function ruleStore() : array
	{
		return $this->validate->ruleStore();
	}

	public function reset() : void
	{
		$this->validate->reset();
	}
}