<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Query;

interface QueryFacadeInterface
{
	/** @return mixed */
	public function querySimple ( string $sql, bool $getString = true );
}