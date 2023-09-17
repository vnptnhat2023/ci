<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\CodeIgniter\Database;

class ThrottleAdapter implements ThrottleAdapterInterface
{
	public function config ( int $type, int $limit_one, int $limit, int $timeout ) : self
	{
		model( ThrottleModelAdapter::class )->config( $type, $limit_one, $limit, $timeout );
		return $this;
	}

	public function getAttempts () : int
	{
		return model( ThrottleModelAdapter::class )->getAttempts();
	}

	public function showCaptcha () : bool
	{
		return model( ThrottleModelAdapter::class )->showCaptcha();
	}

	public function limited (): bool
	{
		return model( ThrottleModelAdapter::class )->limited();
	}

	public function throttle () : int
	{
		return model( ThrottleModelAdapter::class )->throttle();
	}

	public function cleanup () : void
	{
		model( ThrottleModelAdapter::class )->throttle_cleanup();
	}
}