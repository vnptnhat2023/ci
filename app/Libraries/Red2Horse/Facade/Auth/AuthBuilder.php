<?php

declare( strict_types = 1 );
namespace App\Libraries\Red2Horse\Facade\Auth;
use App\Libraries\Red2Horse\Facade\Auth\Config;

class AuthBuilder
{
	private static self $getInstance;

	private array $data = [];

	private Config $config;

	public function __construct ( Config $config )
	{
		$this->config = $config;
	}

	public static function getInstance ( Config $config )
	{
		if ( empty( self::$getInstance ) ) {
			return new self( $config );
		}

		return self::$getInstance;
	}

	public function cache () : self
	{
		$this->data[ 'cache' ] = $this->config->adapter( 'Cache' );
		return $this;
	}

	public function common () : self
	{
		$this->data[ 'common' ] = $this->config->adapter( 'Common' );
		return $this;
	}

	public function config () : self
	{
		$this->data[ 'config' ] = $this->config->adapter( 'Config' );
		return $this;
	}

	public function cookie () : self
	{
		$this->data[ 'cookie' ] = $this->config->adapter( 'Cookie' );
		return $this;
	}

	public function database_user () : self
	{
		$this->data[ 'user' ] = $this->config->adapter( 'Database', 'User' );
		return $this;
	}

	public function database_throttle () : self
	{
		$this->data[ 'throttle' ] = $this->config->adapter( 'Database', 'Throttle' );
		return $this;
	}

	public function mail () : self
	{
		$this->data[ 'mail' ] = $this->config->adapter( 'Mail' );
		return $this;
	}

	public function request () : self
	{
		$this->data[ 'request' ] = $this->config->adapter( 'Request' );
		return $this;
	}

	public function session () : self
	{
		$this->data[ 'session' ] = $this->config->adapter( 'Session' );
		return $this;
	}

	public function validation () : self
	{
		$this->data[ 'validation' ] = $this->config->adapter( 'Validation' );
		return $this;
	}

	public function build ()
	{
		unset( $this->config );
		return AuthComponentBuilder::getInstance()->build( $this->data );
	}

	public function __call ( $methodName, $arguments )
	{
		return $this->data[ $methodName ] ?? $this->$methodName( $arguments );
	}
}