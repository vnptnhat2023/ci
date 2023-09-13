<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\{
	Mixins\Traits\TraitSingleton
};

use function Red2Horse\Mixins\Functions\getConfig;
use function Red2Horse\Mixins\Functions\getInstance;

class AuthBuilder
{
	use TraitSingleton;

	private array $data = [];

	public function cache () : self
	{
		$this->data[ 'cache' ] = getConfig()->adapter( 'Cache' );
		return $this;
	}

	public function common () : self
	{
		$this->data[ 'common' ] = getConfig()->adapter( 'Common' );
		return $this;
	}

	public function config () : self
	{
		$this->data[ 'config' ] = getConfig()->adapter( 'Config' );
		return $this;
	}

	public function cookie () : self
	{
		$this->data[ 'cookie' ] = getConfig()->adapter( 'Cookie' );
		return $this;
	}

	public function database_user () : self
	{
		$this->data[ 'user' ] = getConfig()->adapter( 'Database', 'User' );
		return $this;
	}

	public function database_throttle () : self
	{
		$this->data[ 'throttle' ] = getConfig()->adapter( 'Database', 'Throttle' );
		return $this;
	}

	public function event () : self
	{
		$this->data[ 'event' ] = getConfig()->adapter( 'event' );
		return $this;
	}

	public function mail () : self
	{
		$this->data[ 'mail' ] = getConfig()->adapter( 'Mail' );
		return $this;
	}

	public function request () : self
	{
		$this->data[ 'request' ] = getConfig()->adapter( 'Request' );
		return $this;
	}

	public function session () : self
	{
		$this->data[ 'session' ] = getConfig()->adapter( 'Session' );
		return $this;
	}

	public function validation () : self
	{
		$this->data[ 'validation' ] = getConfig()->adapter( 'Validation' );
		return $this;
	}

	public function build ()
	{
		return getInstance( AuthComponentBuilder::class )->build( $this->data );
	}
}