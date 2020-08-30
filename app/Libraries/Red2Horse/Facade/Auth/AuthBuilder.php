<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\{
	Session\SessionFacadeInterface as session,
	Validation\ValidationFacadeInterface as validation,
	Cookie\CookieFacadeInterface as cookie,
	Cache\CacheFacadeInterface as cache,
	Mail\MailFacadeInterface as mail,
	Request\RequestFacadeInterface as request,
	Database\ThrottleFacadeInterface as throttleModel,
	Database\UserFacadeInterface as userModel,
	Common\CommonFacadeInterface as common,
};

use App\Libraries\Red2Horse\Facade\Auth\Config as R2hConfig;


class AuthBuilder
{
	private throttleModel $throttleModel;
	private userModel $userModel;
	private session $session;
	private cookie $cookie;
	private validation $validation;
	private cache $cache;
	private mail $mail;
	private request $request;
	private common $common;
	private R2hConfig $R2hConfig;

	function __construct( R2hConfig $R2hConfig )
	{
		$this->R2hConfig = new $R2hConfig;
	}

	function cache()
	{
		$cache = $this->R2hConfig->adapter( 'Cache' );
		$this->cache = new $cache();
		return $this;
	}

	function common ()
	{
		$common = $this->R2hConfig->adapter( 'Common' );
		$this->common = new $common();
		return $this;
	}

	function config ()
	{
		$config = $this->R2hConfig->adapter( 'Config' );
		$this->config = new $config();
		return $this;
	}

	function cookie ( cookie $cookie )
	{
		$cookie = $this->R2hConfig->adapter( 'Cookie' );
		$this->cookie = new $cookie();
		return $this;
	}

	function database_user ()
	{
		$database_user = $this->R2hConfig->adapter( 'Database', 'UserAdapter' );
		$this->database_user = new $database_user();
		return $this;
	}

	function database_throttle ()
	{
		$database_throttle = $this->R2hConfig->adapter( 'Database', 'ThrottleAdapter' );
		$this->database_throttle = new $database_throttle();
		return $this;
	}

	function mail ()
	{
		$mail = $this->R2hConfig->adapter( 'Mail' );
		$this->mail = new $mail();
		return $this;
	}

	function request ()
	{
		$request = $this->R2hConfig->adapter( 'Request' );
		$this->request = new $request();
		return $this;
	}

	function session ()
	{
		$session = $this->R2hConfig->adapter( 'Session' );
		$this->session = new $session();
		return $this;
	}

	function validation ()
	{
		$validation = $this->R2hConfig->adapter( 'Validation' );
		$this->validation = new $validation();
		return $this;
	}
}

class AuthBuilderComponent
{
	private throttleModel $throttleModel;
	private userModel $userModel;
	private session $session;
	private cookie $cookie;
	private validation $validation;
	private cache $cache;
	private mail $mail;
	private request $request;
	private common $common;

	static function createBuilder ( $s )
	{
		return new AuthBuilder( $s );
	}

	function __construct( AuthBuilder $b )
	{
		$this->cache = $b->cache( $b->R2hConfig->adapter( 'Cache' ) );
	}
}