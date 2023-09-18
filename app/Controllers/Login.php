<?php

declare( strict_types = 1 );

namespace App\Controllers;

class Login extends BaseController
{
	private \Red2Horse\R2h $auth;
	private ?string $u;
	private ?string $p;
	private bool $r;
	private ?string $c;
	private ?string $e;

	public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
		$request = \Config\Services::request();

		$this->u = $request->getPostGet( 'username' );
		$this->p = $request->getPostGet( 'password' );
		$this->r = ( null !== $request->getPostGet( 'remember_me' ) );
		$this->c = $request->getPostGet( 'captcha' );
		$this->e = $request->getPostGet( 'email' );

		helper( [ 'form', 'form_recaptcha' ] );

		$this->_setting();
	}

	private function _setting ()
	{
		$this->auth->setConfig(
			'BaseConfig',
			static function( $config ) {
				$config->useMultiLogin = false;
				$config->useRememberMe = true;
				return $config;
			}
		);

		$this->auth->setConfig(
			'throttle',
			static function ( $throttle )
			{
				$throttle->useThrottle = true;
				$throttle->throttle->captchaAttempts = 3;
				return $throttle;
			}
		);

		$this->auth->setConfig(
			'CallClass',
			static function ( $callClass )
			{
				$callClass->traitUseBefore = false;
				$callClass->traitUseAfter = true;
				return $callClass;
			}
		);

		$this->auth->setConfig(
			'Cookie',
			static function ( $cookie )
			{
				$cookie->cookie = 'abc';
				$cookie->ttl = 600;
				return $cookie;
			}
		);

		$this->auth->setConfig(
			'session',
			static function ( $session )
			{
				$session->session = 'abc';
				$session->sessionTimeToUpdate = 600;
				return $session;
			}
		);
	}

	public function index ()
	{
		$this->auth->login( $this->u, $this->p, $this->r, $this->c );
		return view( 'login/login', ( array ) $this->auth->getMessage() );
	}

	public function forgot ()
	{
		$this->auth->requestPassword( $this->u, $this->e, $this->c );
		return view( 'login/forgot', ( array ) $this->auth->getMessage() );
	}

	public function logout ()
	{
		$this->auth->logout();
		return view( 'login/login', ( array ) $this->auth->getMessage() );
	}
}