<?php
declare(strict_types = 1);
# @Todo: Captcha
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
		$u = $this->u; $p = $this->p; $r = null !== $this->r; $c = $this->c;

		$this->auth->login( $u, $p, $r, $c );

		if ( env( 'environment' ) !== 'production' ) {
			$form = [
				'form' => [
					'username' => $u,
					'password' => $p,
					'captcha' => $c,
					'remember_me' => $r
				]
			];

			d( $this->auth->getMessage( $form ) );
		}

		return view( 'login/login', (array) $this->auth->getMessage() );
	}

	public function forgot ()
	{
		$u = $this->u;
		$e = $this->e;
		$c = $this->c;

		if ( env( 'environment' ) !== 'production' ) {
			$form = [
				'form' => [
					'username' => $u,
					'email' => $e,
					'captcha' => $c,
				]
			];

			d( $this->auth->getMessage( $form ) );
		}

		$this->auth->requestPassword( $u, $e, $c );
		return view( 'login/forgot', (array) $this->auth->getMessage() );
	}

	public function logout ()
	{
		$this->auth->logout();
		return view( 'login/login', (array) $this->auth->getMessage() );
	}
}