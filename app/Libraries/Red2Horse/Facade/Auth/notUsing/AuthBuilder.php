<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\{
	Facade\Auth\Config,
	Mixins\Traits\TraitSingleton
};

use function Red2Horse\Mixins\Functions\getInstance;

class AuthBuilder
{
	use TraitSingleton;

	private array $data = [];

	public function cache () : self
	{
		$this->data[ 'cache' ] = getInstance( Config::class )->adapter( 'Cache' );
		return $this;
	}

	public function common () : self
	{
		$this->data[ 'common' ] = getInstance( Config::class )->adapter( 'Common' );
		return $this;
	}

	public function config () : self
	{
		$this->data[ 'config' ] = getInstance( Config::class )->adapter( 'Config' );
		return $this;
	}

	public function cookie () : self
	{
		$this->data[ 'cookie' ] = getInstance( Config::class )->adapter( 'Cookie' );
		return $this;
	}

	public function database_user () : self
	{
		$this->data[ 'user' ] = getInstance( Config::class )->adapter( 'Database', 'User' );
		return $this;
	}

	public function database_throttle () : self
	{
		$this->data[ 'throttle' ] = getInstance( Config::class )->adapter( 'Database', 'Throttle' );
		return $this;
	}

	public function event () : self
	{
		$this->data[ 'event' ] = getInstance( Config::class )->adapter( 'event' );
		return $this;
	}

	public function mail () : self
	{
		$this->data[ 'mail' ] = getInstance( Config::class )->adapter( 'Mail' );
		return $this;
	}

	public function request () : self
	{
		$this->data[ 'request' ] = getInstance( Config::class )->adapter( 'Request' );
		return $this;
	}

	public function session () : self
	{
		$this->data[ 'session' ] = getInstance( Config::class )->adapter( 'Session' );
		return $this;
	}

	public function validation () : self
	{
		$this->data[ 'validation' ] = getInstance( Config::class )->adapter( 'Validation' );
		return $this;
	}

	public function build ()
	{
		return getInstance( AuthComponentBuilder::class )->build( $this->data );
	}
}