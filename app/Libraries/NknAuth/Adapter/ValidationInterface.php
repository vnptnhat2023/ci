<?php

namespace App\Libraries\NknAuth\Adapter;

interface ValidationInterface
{
	public function isValid ( array $data, array $rules ) : bool;

	public function getErrors( string $field = null ) : array;
}