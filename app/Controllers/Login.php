<?php

declare(strict_types = 1);

namespace App\Controllers;

use Red2Horse\R2h;

class Login extends BaseController
{
	private R2h $auth;
	private ?string $u;
	private ?string $p;
	private bool $r;
	private ?string $c;
	private ?string $e;

	public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
		$request = \Config\Services::request();

		$this->u = $request->getPostGet('username');
		$this->p = $request->getPostGet('password');
		$this->r = null !== $request->getPostGet('remember_me');
		$this->c = $request->getPostGet('captcha');
		$this->e = $request->getPostGet('email');

		helper( [ 'form', 'form_recaptcha' ] );
	}

	public function index()
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