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

	public function __construct()
	{
		$this->R2hConfig = new R2hConfig;
	}

	public function cache()
	{
		$cache = $this->R2hConfig->adapter( 'Cache' );
		$this->cache = new $cache();
		return $this;
	}

	public function common ()
	{
		$common = $this->R2hConfig->adapter( 'Common' );
		$this->common = new $common();
		return $this;
	}

	public function config ()
	{
		$config = $this->R2hConfig->adapter( 'Config' );
		$this->config = new $config();
		return $this;
	}

	public function cookie ()
	{
		$cookie = $this->R2hConfig->adapter( 'Cookie' );
		$this->cookie = new $cookie();
		return $this;
	}

	public function database_user ()
	{
		$database_user = $this->R2hConfig->adapter( 'Database', 'User' );
		$this->database_user = new $database_user();
		return $this;
	}

	public function database_throttle ()
	{
		$database_throttle = $this->R2hConfig->adapter( 'Database', 'Throttle' );
		$this->database_throttle = new $database_throttle();
		// die(var_dump($this->database_throttle));
		return $this;
	}

	public function mail ()
	{
		$mail = $this->R2hConfig->adapter( 'Mail' );
		$this->mail = new $mail();
		return $this;
	}

	public function request ()
	{
		$request = $this->R2hConfig->adapter( 'Request' );
		$this->request = new $request();
		return $this;
	}

	public function session ()
	{
		$session = $this->R2hConfig->adapter( 'Session' );
		$this->session = new $session();
		return $this;
	}

	public function validation ()
	{
		$validation = $this->R2hConfig->adapter( 'Validation' );
		$this->validation = new $validation();
		return $this;
	}

	public function build ()
	{
		return new AuthBuilderComponent( $this );
	}
}