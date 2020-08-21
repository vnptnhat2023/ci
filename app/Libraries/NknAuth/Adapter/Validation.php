<?php

namespace App\Libraries\NknAuth\Adapter;

class Validation implements ValidationInterface
{
	protected ValidationInterface $validation;

	public function __construct( ValidationInterface $validation )
	{
		$this->validation = $validation;
	}

	public function isValid ( array $data, array $rules ): bool
	{
		return true;
	}
}