<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\CodeIgniter\Common;
use Red2Horse\Facade\Common\CommonFacadeInterface;

interface CommonAdapterInterface extends CommonFacadeInterface
{
	public function cache( ?string $key = null );
}